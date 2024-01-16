/* eslint-disable no-undef */
// eslint-disable-next-line import/no-extraneous-dependencies
import { memo, useCallback, useState, useEffect } from 'react'
import { __ } from '../Utils/i18nwrap'
import SnackMsg from '../components/Utilities/SnackMsg'
import Table from '../components/Utilities/Table'
import bitsFetch from '../Utils/bitsFetch'
import OptionMenu from '../components/Utilities/OptionMenu'
import EditIcn from '../Icons/EditIcn'
import ReviewsEdit from '../components/ReviewsEdit'
import { $integrationDetails } from '../Utils/GlobalStates'
import { useRecoilState } from 'recoil'

function FormDetails({ newFormId }) {
    const [snack, setSnackbar] = useState({ show: false })
    const [tableData, setTableData] = useState(bitwelzp.reviewsDetails)
    const [showEditModal, setShowEditModal] = useState(false)
    const [integConfig] = useRecoilState($integrationDetails)
    const [rowId, setRowId] = useState()

    const [cols, setCols] = useState([
        { width: 200, minWidth: 20, Header: __('Zoho ID', 'bitwelzp'), accessor: 'zoho_id', Cell: e => JSON.parse(e.row.original.form_details)?.zoho_id },
        { width: 180, minWidth: 20, Header: __('Employee Name', 'bitwelzp'), accessor: 'employee_name', Cell: e => JSON.parse(e.row.original.form_details)?.employee_name ? JSON.parse(e.row.original.form_details)?.employee_name : '' },
        { width: 200, minWidth: 20, Header: __('First Name', 'bitwelzp'), accessor: 'fname', Cell: e => JSON.parse(e.row.original.form_details)?.fname },
        { width: 200, minWidth: 20, Header: __('Last Name', 'bitwelzp'), accessor: 'lname', Cell: e => JSON.parse(e.row.original.form_details)?.lname },
        { width: 80, minWidth: 20, Header: __('Rating', 'bitwelzp'), accessor: 'rating', Cell: e => JSON.parse(e.row.original.form_details)?.star },
        { width: 250, minWidth: 80, Header: __('Phrases', 'bitwelzp'), accessor: 'phrases', Cell: e => JSON.parse(e.row.original.form_details)?.phrases?.length ? JSON.parse(e.row.original.form_details)?.phrases?.map(item => `${item} , `) : '' },
        { width: 250, minWidth: 100, Header: __('Title', 'bitwelzp'), accessor: 'title', Cell: e => JSON.parse(e.row.original.form_details)?.title },
        { width: 250, minWidth: 100, Header: __('Title Description', 'bitwelzp'), accessor: 'description', Cell: e => JSON.parse(e.row.original.form_details)?.desc },
        { width: 150, minWidth: 20, Header: __('Age Range', 'bitwelzp'), accessor: 'age', Cell: e => JSON.parse(e.row.original.form_details)?.age },
        { width: 150, minWidth: 20, Header: __('Gender', 'bitwelzp'), accessor: 'gender', Cell: e => JSON.parse(e.row.original.form_details)?.gender },
        { width: 150, minWidth: 20, Header: __('Status', 'bitwelzp'), accessor: 'status', Cell: e => <button type='button' className={`btn btcd-btn-lg ${JSON.parse(e.row.original.form_details).status === 'pending' ? 'red' : 'green'} sh-sm flx`} onClick={() => handleApprove(e.row.original.id)}>{JSON.parse(e.row.original.form_details)?.status}</button> },
        { width: 150, minWidth: 20, Header: __('Created At', 'bitwelzp'), accessor: 'created_at', Cell: e => e.row.original?.created_at },

    ])


    const setTableCols = useCallback(newCols => { setCols(newCols) }, [])
    
    const handleDelete = (selectedRowIds) => {
        const Ids = []
        selectedRowIds.map(item => {
            Ids.push(item.original.id)
        })
        bitsFetch(Ids, 'delete_form_details')
            .then(response => {
                if (response) {
                    const filteredData = tableData.filter(row => !Ids.includes(row.id))
                    setTableData(filteredData)
                    setSnackbar({ show: true, msg: __('Successfully deleted', 'bitwelzp') })
                    bitsFetch(integConfig?.integ_config?.auth_details, 'get_peoples_forms')
                        .then(response => {
                            console.log(response)
                        })
                }
            })
    }

    const handleApprove = (selectedRowId) => {
        bitsFetch(selectedRowId, 'review_approve')
            .then(response => {
                if (response) {
                    setTableData(response.data)
                    setSnackbar({ show: true, msg: __('Successfully Updated', 'bitwelzp') })
                    bitsFetch(integConfig?.integ_config?.auth_details, 'get_peoples_forms')
                        .then(response => {
                            console.log(response)
                        })
                }
            })
    }

    const handleEditModal = (selectedRowId) => {
        setRowId(selectedRowId)
        setShowEditModal(true)
    }

    useEffect(() => {
        const ncols = cols.filter(itm => itm.accessor !== 't_action')
        // eslint-disable-next-line max-len
        ncols.push({
            sticky: 'right',
            width: 100,
            minWidth: 60,
            Header: 'Actions',
            accessor: 't_action',
            Cell: val => (
                <>
                    <OptionMenu title="Actions" w={150} h={164}>
                        <button type="button" onClick={() => handleEditModal(val.cell.row.original.id)}>
                            <EditIcn size={18} />
                            &nbsp;Edit
                        </button>
                    </OptionMenu>
                </>
            ),
        })
        setCols([...ncols])
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [newFormId])




    return (
        <div id="all-forms">
            <SnackMsg snack={snack} setSnackbar={setSnackbar} />
            <>
                <div className="forms">
                    <Table
                        className="f-table btcd-all-frm"
                        height={500}
                        columns={cols}
                        data={tableData}
                        setTableData={setTableData}
                        rowSeletable
                        newFormId={newFormId}
                        resizable
                        columnHidable
                        setTableCols={setTableCols}
                        search
                        handleDelete={handleDelete}
                    />
                </div>

                {
                    showEditModal && (
                        <ReviewsEdit close={setShowEditModal} rowId={rowId} setTableData={setTableData} tableData={tableData} setSnackbar={setSnackbar} />)
                }
            </>
        </div>
    )
}

export default memo(FormDetails)
