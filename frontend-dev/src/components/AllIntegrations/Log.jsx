import { lazy, useEffect, useState, useCallback, useRef, memo } from 'react'
import { Link, useParams } from 'react-router-dom'
import { __ } from '../../Utils/i18nwrap'
import SnackMsg from '../Utilities/SnackMsg'
import Table from "../Utilities/Table"
import bitsFetch from "../../Utils/bitsFetch"
const ZohoCRMAuthorization = lazy(() => import('./ZohoCRM/ZohoCRMAuthorization'))
const Loader = lazy(() => import('../Loaders/Loader'))
import CopyText from '../Utilities/CopyText'

function Log({ allIntegURL, integrations }) {
  const { id } = useParams()
  const [snack, setSnackbar] = useState({ show: false })
  const fetchIdRef = useRef(0)
  const [confMdl, setconfMdl] = useState({ show: false })
  const [pageCount, setPageCount] = useState(0)
  const [countEntries, setCountEntries] = useState(0)
  const [isloading, setisloading] = useState(false)
  const [log, setLog] = useState([])
  const [cols, setCols] = useState([
    { width: 250, minWidth: 80, Header: __('Status', 'bitwelzp'), accessor: 'response_type'},
    { width: 250, minWidth: 80, Header: __('Record Type', 'bitwelzp'), accessor: 'api_type'},
    { width: 220, minWidth: 200, Header: __('Response', 'bitwelzp'), accessor: 'response_obj', Cell: val => <CopyText value={val.row.values.response_obj} setSnackbar={setSnackbar} className="cpyTxt" /> },
    { width: 220, minWidth: 200, Header: __('Date', 'bitwelzp'), accessor: 'created_at' },
 ])
 const setTableCols = useCallback(newCols => { setCols(newCols) }, [])
  // route is info/:id but for redirect uri need to make new/:type
  let location = window.location.toString()

  const toReplaceInd = location.indexOf('/info')
  location = window.encodeURI(`${location.slice(0, toReplaceInd)}/new/${integrations[id].type}`)

  const closeConfMdl = useCallback(() => {
    confMdl.show = false
    setconfMdl({ ...confMdl })
  }, [confMdl])



  const setBulkDelete = useCallback((rows, action) => {
    const rowID = []
    const entries = []
    if (typeof rows[0] === 'object') {
      for (let i = 0; i < rows.length; i += 1) {
        rowID.push(rows[i].id)
        entries.push(rows[i].original.id)
      }
    } else {
      rowID.push(rows.id)
      entries.push(rows.original.id)
    }
    const ajaxData = { id: entries }
    console.log('ajaxData', ajaxData, entries);
    bitsFetch(ajaxData, 'log/delete').then((res) => {
      if (res.success) {
        if (action && action.fetchData && action.data) {
          action.fetchData(action.data)
        }
        setSnackbar({ show: true, msg: res.data })
      }
    })
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  const delConfMdl = useCallback(
    (row, data) => {
      if (row.idx !== undefined) {
        // eslint-disable-next-line no-param-reassign
        row.id = row.idx
        // eslint-disable-next-line no-param-reassign
        row.original = row.data[0].row.original
      }
      confMdl.btnTxt = 'Delete'
      confMdl.body = 'Are you sure to delete this entry'
      confMdl.btnClass = ''

      confMdl.action = () => {
        setBulkDelete(row, data)
        closeConfMdl()
      }
      confMdl.show = true
      setconfMdl({ ...confMdl })
    },
    [closeConfMdl, confMdl, setBulkDelete],
  )


  const fetchData = useCallback(
    ({ pageSize, pageIndex }) => {
      console.log('dasddalfhalsfla/log')
      // eslint-disable-next-line no-plusplus
      /* if (refreshResp) {
        setisloading(true)
        return
      } */

      // eslint-disable-next-line no-plusplus
      const fetchId = ++fetchIdRef.current
      if (log.length < 1) {
        setisloading(true)
      }
      if (fetchId === fetchIdRef.current) {
        const startRow = pageSize * pageIndex
        bitsFetch(
          {
            id: integrations[id].id,
            offset: startRow,
            pageSize
          },
          'log/get',
        ).then((res) => {
          if (res?.success) {
            setPageCount(Math.ceil(res.data.count / pageSize))
            setCountEntries(res.data.count)
            setLog(res.data.data)
          }
          setisloading(false)
        })
      }
      // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [delConfMdl, id],
  )
  return (
    <>
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <div className="flx">
        <Link to={allIntegURL} className="btn btcd-btn-o-gray">
          <span className="btcd-icn icn-chevron-left" />
          &nbsp;Back
        </Link>
        <div className="w-8 txt-center">
          <b className="f-lg">{integrations[id].type}</b>
          <div>{__('Integration Log', 'bitwelzp')}</div>
        </div>
      </div>

      <div className="forms">
        <Table
          className="f-table btcd-all-frm"
          height={500}
          columns={cols}
          data={log}
          loading={isloading}
          countEntries={countEntries}
          rowSeletable
          resizable
          columnHidable
          setTableCols={setTableCols}
          setBulkDelete={setBulkDelete}
          fetchData={fetchData}
          setBulkDelete={setBulkDelete}
          pageCount={pageCount}
        />
      </div>
    </>
  )
}

export default memo(Log)