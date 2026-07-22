/* eslint-disable no-undef */
// eslint-disable-next-line import/no-extraneous-dependencies
import { memo, useCallback, useState, useEffect } from 'react'
import { useAtom } from 'jotai'
import { __ } from '../Utils/i18nwrap'
import SnackMsg from '../components/Utilities/SnackMsg'
import Table from '../components/Utilities/Table'
import bitsFetch from '../Utils/bitsFetch'
import OptionMenu from '../components/Utilities/OptionMenu'
import EditIcn from '../Icons/EditIcn'
import ReviewsEdit from '../components/ReviewsEdit'
import { $integrationDetails } from '../Utils/GlobalStates'
import { mergeSavedCols } from '../Utils/tableColPersist'

const parseDetails = (row) => {
  try {
    return JSON.parse(row.form_details) || {}
  } catch {
    return {}
  }
}

function FormDetails({ newFormId }) {
  const [snack, setSnackbar] = useState({ show: false })
  const [tableData, setTableData] = useState(bitwelzp.reviewsDetails)
  const [showEditModal, setShowEditModal] = useState(false)
  const [integConfig] = useAtom($integrationDetails)
  const [rowId, setRowId] = useState()

  const [cols, setCols] = useState(() => mergeSavedCols('form_details', [
    {
      width: 200,
      minWidth: 20,
      Header: __('Zoho ID', 'bitwelzp'),
      id: 'zoho_id',
      accessor: (row) => parseDetails(row).zoho_id ?? '',
    },
    {
      width: 180,
      minWidth: 20,
      Header: __('Employee Name', 'bitwelzp'),
      id: 'employee_name',
      accessor: (row) => parseDetails(row).employee_name ?? '',
    },
    {
      width: 200,
      minWidth: 20,
      Header: __('First Name', 'bitwelzp'),
      id: 'fname',
      accessor: (row) => parseDetails(row).fname ?? '',
    },
    {
      width: 200,
      minWidth: 20,
      Header: __('Last Name', 'bitwelzp'),
      id: 'lname',
      accessor: (row) => parseDetails(row).lname ?? '',
    },
    {
      width: 80,
      minWidth: 20,
      Header: __('Rating', 'bitwelzp'),
      id: 'rating',
      accessor: (row) => parseDetails(row).star ?? '',
    },
    {
      width: 250,
      minWidth: 80,
      Header: __('Phrases', 'bitwelzp'),
      id: 'phrases',
      accessor: (row) => (parseDetails(row).phrases?.length
        ? parseDetails(row).phrases.join(', ')
        : ''),
    },
    {
      width: 250,
      minWidth: 100,
      Header: __('Title', 'bitwelzp'),
      id: 'title',
      accessor: (row) => parseDetails(row).title ?? '',
    },
    {
      width: 250,
      minWidth: 100,
      Header: __('Title Description', 'bitwelzp'),
      id: 'description',
      accessor: (row) => parseDetails(row).desc ?? '',
    },
    {
      width: 150,
      minWidth: 20,
      Header: __('Age Range', 'bitwelzp'),
      id: 'age',
      accessor: (row) => parseDetails(row).age ?? '',
    },
    {
      width: 150,
      minWidth: 20,
      Header: __('Gender', 'bitwelzp'),
      id: 'gender',
      accessor: (row) => parseDetails(row).gender ?? '',
    },
    {
      width: 150,
      minWidth: 20,
      Header: __('Status', 'bitwelzp'),
      id: 'status',
      accessor: (row) => parseDetails(row).status ?? '',
      Cell: (e) => (
        <button
          type="button"
          className={`status-badge ${
            e.value === 'pending' ? 'inactive' : 'active'
          }`}
          onClick={() => handleApprove(e.row.original.id)}
        >
          {e.value}
        </button>
      ),
    },
    {
      width: 150,
      minWidth: 20,
      Header: __('Created At', 'bitwelzp'),
      accessor: 'created_at',
    },
    {
      width: 150,
      minWidth: 20,
      Header: __('Updated At', 'bitwelzp'),
      accessor: 'updated_at',
      Cell: (e) => e.value || '—',
    },
  ]))

  const setTableCols = useCallback((newCols) => {
    setCols(newCols)
  }, [])

  const handleDelete = (selectedRowIds) => {
    const Ids = []
    selectedRowIds.map((item) => {
      Ids.push(item.original.id)
    })
    bitsFetch(Ids, 'delete_form_details').then((response) => {
      if (response) {
        const filteredData = tableData.filter((row) => !Ids.includes(row.id))
        setTableData(filteredData)
        setSnackbar({
          show: true,
          msg: __('Successfully deleted', 'bitwelzp'),
        })
        bitsFetch(
          integConfig?.integ_config?.auth_details,
          'get_peoples_forms',
        ).then((response) => {
          console.log(response)
        })
      }
    })
  }

  const handleApprove = (selectedRowId) => {
    bitsFetch(selectedRowId, 'review_approve').then((response) => {
      if (response) {
        setTableData(response.data)
        setSnackbar({
          show: true,
          msg: __('Successfully Updated', 'bitwelzp'),
        })
        bitsFetch(
          integConfig?.integ_config?.auth_details,
          'get_peoples_forms',
        ).then((response) => {
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
    const ncols = cols.filter((itm) => itm.accessor !== 't_action')
    // eslint-disable-next-line max-len
    ncols.push({
      sticky: 'right',
      width: 100,
      minWidth: 60,
      Header: 'Actions',
      accessor: 't_action',
      Cell: (val) => (
        <>
          <OptionMenu title="Actions" w={150} h={164}>
            <button
              type="button"
              onClick={() => handleEditModal(val.cell.row.original.id)}
            >
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
            tableName="form_details"
            handleDelete={handleDelete}
          />
        </div>

        {showEditModal && (
          <ReviewsEdit
            close={setShowEditModal}
            rowId={rowId}
            setTableData={setTableData}
            tableData={tableData}
            setSnackbar={setSnackbar}
          />
        )}
      </>
    </div>
  )
}

export default memo(FormDetails)
