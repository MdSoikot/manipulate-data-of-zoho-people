/* eslint-disable no-undef */
// eslint-disable-next-line import/no-extraneous-dependencies
import { memo, useCallback, useState, useEffect } from 'react'
import { __ } from '../Utils/i18nwrap'
import SnackMsg from '../components/Utilities/SnackMsg'
import Table from '../components/Utilities/Table'
import CopyText from '../components/Utilities/CopyText'
import bitsFetch from '../Utils/bitsFetch'
import { $integrationDetails } from '../Utils/GlobalStates'
import { useRecoilState } from 'recoil'
import LoaderSm from '../components/Loaders/LoaderSm'

function AllEmployees({ newFormId }) {
  const [snack, setSnackbar] = useState({ show: false })
  const [integrationDetails] = useRecoilState($integrationDetails)
  const integConfig = integrationDetails.integ_config
  const [isLoading, setisLoading] = useState(false)
  const [tableData, setTableData] = useState(bitwelzp.all_employees)

  const [cols, setCols] = useState([
    { width: 150, minWidth: 80, Header: __('Employee ID', 'bitwelzp'), accessor: 'employee_id', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.employee_id}</a> : e.row.original.employee_id },
    { width: 300, minWidth: 80, Header: __('Review Form Link', 'bitwelzp'), accessor: 'review_form_link', Cell: e => <CopyText value={`https://wellqor.com/therapist-review-form/?employee_id=${e.row.original.employee_id}`} setSnackbar={setSnackbar} className="cpyTxt" readOnly={true} /> },
    { width: 250, minWidth: 80, Header: __('Employee Status', 'bitwelzp'), accessor: 'employee_status', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.employee_status}</a> : e.row.original.employee_status },
    { width: 250, minWidth: 80, Header: __('First Name', 'bitwelzp'), accessor: 'fname', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.fname}</a> : e.row.original.fname },
    { width: 250, minWidth: 80, Header: __('Last Name', 'bitwelzp'), accessor: 'lname', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.lname}</a> : e.row.original.lname },
    { width: 250, minWidth: 80, Header: __('Medical Qualification', 'bitwelzp'), accessor: 'medical_qualification', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.medical_qualification}</a> : e.row.original.medical_qualification },
    { width: 250, minWidth: 80, Header: __('Designation', 'bitwelzp'), accessor: 'designation', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.designation}</a> : e.row.original.designation },
    { width: 250, minWidth: 80, Header: __('Languages', 'bitwelzp'), accessor: 'language', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.languages}</a> : e.row.original.languages },
    { width: 250, minWidth: 80, Header: __('Certifications', 'bitwelzp'), accessor: 'certifications', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.certifications}</a> : e.row.original.certifications },
    { width: 250, minWidth: 80, Header: __('Cultural Competency', 'bitwelzp'), accessor: 'cultural_competency', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.cultural_competency}</a> : e.row.original.cultural_competency },
    { width: 250, minWidth: 80, Header: __('Public Bio', 'bitwelzp'), accessor: 'public_bio', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.public_bio}</a> : e.row.original.public_bio },
    { width: 250, minWidth: 80, Header: __('Licensed In', 'bitwelzp'), accessor: 'licensed_in', Cell: e => e.row.original.page_status === 'active' ? <a className="btcd-tabl-lnk" href={`https://wellqor.com/${e.row.original.fname.charAt(0)}${e.row.original.lname}`} target="_blank">{e.row.original.licensed_in}</a> : e.row.original.licensed_in },
    { width: 150, minWidth: 20, Header: __('Page Status', 'bitwelzp'), accessor: 'page_status', Cell: e => <button type='button' className={`btn btcd-btn-lg ${e.row.original.page_status === 'inactive' ? 'red' : 'green'} sh-sm flx`} onClick={() => handleActive(e.row.original.id)}>{e.row.original.page_status}</button> },


  ])


  const handleActive = (selectedRowId) => {
    bitsFetch(selectedRowId, 'page_active')
      .then(response => {
        if (response) {
          setTableData(response.data)
          setSnackbar({ show: true, msg: __('Successfully Updated', 'bitwelzp') })
        }
      })
  }




  const setTableCols = useCallback(newCols => { setCols(newCols) }, [])

  const fetchData = () => {
    setisLoading(true)
    integConfig.auth_details.integrationId = integConfig?.integration_details?.id
    bitsFetch(integConfig?.auth_details, 'get_peoples_forms')
      .then(result => {
        setisLoading(false)
        if (result?.success) {
          if (result.data.length > tableData.length) {
            setSnackbar({ show: true, msg: __('New Empolyee Added and Data Updated', 'bitwelzp') })
          }
          else {
            setSnackbar({ show: true, msg: __('Successfully Updated', 'bitwelzp') })
          }
          setTableData(result.data)
        } else if ((result?.data?.data) || (!result.success && typeof result.data === 'string')) {
          setSnackbar({ show: true, msg: `${__('Data fetch failed Cause:', 'bitwelzp')}${result.data.data || result.data}. ${__('please try again', 'bitwelzp')}` })
        }
        else {
          setSnackbar({ show: true, msg: __('Data fetch failed. please try again', 'bitwelzp') })
        }
      }
      )
      .catch(() => setisLoading(false))
  }

  useEffect(() => {
    if (!integConfig.auth_details.isAuthorized) {
      setSnackbar({ show: true, msg: __('Please complete the authorization first', 'bitwelzp') })
    }

  }, [])

  const DataFetchBtn = () => {
    return (
      <button style={{ marginLeft: '10px', marginRight: '10px' }} disabled={!integConfig?.auth_details?.isAuthorized} onClick={fetchData} type="button" className="btn btcd-btn-lg green sh-sm flx">{__('Fetch data', 'bitwelzp')}
        {isLoading && <LoaderSm size="20" clr="#022217" className="ml-2" />}
      </button>
    )
  }

  const handleDelete = (selectedRowIds) => {
    const Ids = []
    selectedRowIds.map(item => {
      Ids.push(item.original.id)
    })
    bitsFetch(Ids, 'delete_employees')
      .then(response => {
        if (response) {
          const filteredData = tableData.filter(row => !Ids.includes(row.id))
          setTableData(filteredData)
          setSnackbar({ show: true, msg: __('Successfully deleted', 'bitwelzp') })
        }
      })
  }



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
            DataFetchBtn={<DataFetchBtn />}
            handleDelete={handleDelete}
          />
        </div>
      </>
    </div>
  )
}

export default memo(AllEmployees)
