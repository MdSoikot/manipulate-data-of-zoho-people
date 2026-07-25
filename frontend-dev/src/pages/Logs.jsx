import { memo, useCallback, useEffect, useState } from 'react'
import { __ } from '../Utils/i18nwrap'
import SnackMsg from '../components/Utilities/SnackMsg'
import Table from '../components/Utilities/Table'
import TableLoader from '../components/Loaders/TableLoader'
import ConfirmModal from '../components/Utilities/ConfirmModal'
import CloseIcn from '../Icons/CloseIcn'
import bitsFetch from '../Utils/bitsFetch'
import { mergeSavedCols } from '../Utils/tableColPersist'

const ACTION_LABELS = {
  employee_delete: __('Employee Deleted', 'bitwelzp'),
  employee_page_status: __('Page Status Changed', 'bitwelzp'),
  employee_sync: __('Employee Sync', 'bitwelzp'),
  review_add: __('Review Added', 'bitwelzp'),
  review_edit: __('Review Updated', 'bitwelzp'),
  review_delete: __('Review Deleted', 'bitwelzp'),
  review_status: __('Review Status Changed', 'bitwelzp'),
  auth_save: __('Authorization Saved', 'bitwelzp'),
  auth_update: __('Authorization Updated', 'bitwelzp'),
}

const formatValue = (value) => {
  if (value === null || value === undefined || value === '') return '—'
  if (Array.isArray(value)) return value.join(', ')
  if (typeof value === 'object') return JSON.stringify(value)
  return String(value)
}

const parseState = (raw) => {
  if (!raw) return null
  try {
    const state = JSON.parse(raw)
    return state && typeof state === 'object' ? state : null
  } catch {
    return null
  }
}

const stateKeys = (oldState, newState) => {
  const keys = []
  const push = (obj) => {
    if (obj && typeof obj === 'object') {
      Object.keys(obj).forEach((k) => {
        if (!keys.includes(k)) keys.push(k)
      })
    }
  }
  push(oldState)
  push(newState)
  return keys
}

const sx = {
  overlay: { position: 'fixed', inset: 0, zIndex: 8 },
  metaGrid: { display: 'grid', gridTemplateColumns: '110px 1fr', rowGap: 8, columnGap: 10, fontSize: 13, margin: '14px 0' },
  metaLabel: { color: '#777' },
  sectionTitle: { fontSize: 13, fontWeight: 600, margin: '18px 0 8px', textTransform: 'uppercase', letterSpacing: 0.4, color: '#555' },
  tbl: { width: '100%', borderCollapse: 'collapse', fontSize: 13, tableLayout: 'fixed' },
  th: { textAlign: 'left', padding: '7px 8px', borderBottom: '1px solid #e3e3e3', color: '#555', fontWeight: 600 },
  td: { padding: '7px 8px', borderBottom: '1px solid #f0f0f0', verticalAlign: 'top', wordBreak: 'break-word' },
  changed: { background: '#fff8e1' },
  oldVal: { color: '#b3372c', textDecoration: 'line-through' },
  newVal: { color: '#1a7f37' },
}

function StateDiff({ oldState, newState }) {
  const hasOld = oldState && typeof oldState === 'object'
  const hasNew = newState && typeof newState === 'object'

  if (!hasOld && !hasNew) return null

  // Single-state snapshot (created or deleted records)
  if (!hasOld || !hasNew) {
    const state = hasOld ? oldState : newState
    return (
      <>
        <div style={sx.sectionTitle}>
          {hasOld ? __('Previous State', 'bitwelzp') : __('Current State', 'bitwelzp')}
        </div>
        <table style={sx.tbl}>
          <tbody>
            {stateKeys(state, null).map((key) => (
              <tr key={key}>
                <td style={{ ...sx.td, width: '38%', color: '#777' }}>{key}</td>
                <td style={sx.td}>{formatValue(state[key])}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </>
    )
  }

  const changedKeys = stateKeys(oldState, newState)
    .filter((key) => formatValue(oldState[key]) !== formatValue(newState[key]))

  if (!changedKeys.length) return null

  return (
    <table style={sx.tbl}>
      <thead>
        <tr>
          <th style={{ ...sx.th, width: '28%' }}>{__('Field', 'bitwelzp')}</th>
          <th style={sx.th}>{__('Previous', 'bitwelzp')}</th>
          <th style={sx.th}>{__('Current', 'bitwelzp')}</th>
        </tr>
      </thead>
      <tbody>
        {changedKeys.map((key) => (
          <tr key={key} style={sx.changed}>
            <td style={{ ...sx.td, color: '#777' }}>{key}</td>
            <td style={{ ...sx.td, ...sx.oldVal }}>{formatValue(oldState[key])}</td>
            <td style={{ ...sx.td, ...sx.newVal }}>{formatValue(newState[key])}</td>
          </tr>
        ))}
      </tbody>
    </table>
  )
}

function LogDetailsDrawer({ log, close }) {
  const oldState = parseState(log?.before_state)
  const newState = parseState(log?.after_state)

  return (
    <>
      {log && <div style={sx.overlay} onClick={close} role="presentation" />}
      <div className="btcd-drawer" style={{ right: log ? 0 : -500, width: 480 }}>
        {log && (
          <>
            <div className="flx flx-between">
              <div className="btcd-drawer-title">
                {ACTION_LABELS[log.action] || log.action}
              </div>
              <button
                onClick={close}
                className="icn-btn btcd-icn-lg"
                aria-label="drawer-close"
                type="button"
              >
                <CloseIcn size="14" stroke="3" />
              </button>
            </div>
            <div className="btcd-mdl-div" />

            <div style={sx.metaGrid}>
              <span style={sx.metaLabel}>{__('Entity', 'bitwelzp')}</span>
              <span>{log.entity_type}</span>
              <span style={sx.metaLabel}>{__('User', 'bitwelzp')}</span>
              <span>{log.user_name}</span>
              <span style={sx.metaLabel}>{__('IP', 'bitwelzp')}</span>
              <span>{log.ip}</span>
              <span style={sx.metaLabel}>{__('Date', 'bitwelzp')}</span>
              <span>{log.created_at}</span>
            </div>

            <StateDiff oldState={oldState} newState={newState} />

            {!oldState && !newState && (
              <div style={{ ...sx.sectionTitle, fontWeight: 400, textTransform: 'none' }}>
                {__('No state details recorded for this entry.', 'bitwelzp')}
              </div>
            )}
          </>
        )}
      </div>
    </>
  )
}

function Logs() {
  const [snack, setSnackbar] = useState({ show: false })
  const [tableData, setTableData] = useState([])
  const [loading, setLoading] = useState(true)
  const [showClearModal, setShowClearModal] = useState(false)
  const [selectedLog, setSelectedLog] = useState(null)

  const [cols, setCols] = useState(() => mergeSavedCols('logs', [
    {
      width: 190,
      minWidth: 60,
      Header: __('Action', 'bitwelzp'),
      id: 'action',
      accessor: (row) => ACTION_LABELS[row.action] || row.action,
    },
    {
      width: 110,
      minWidth: 60,
      Header: __('Entity', 'bitwelzp'),
      accessor: 'entity_type',
    },
    {
      width: 150,
      minWidth: 60,
      Header: __('User', 'bitwelzp'),
      accessor: 'user_name',
    },
    {
      width: 140,
      minWidth: 60,
      Header: __('IP', 'bitwelzp'),
      accessor: 'ip',
    },
    {
      width: 170,
      minWidth: 60,
      Header: __('Date', 'bitwelzp'),
      accessor: 'created_at',
    },
  ]))

  const setTableCols = useCallback((newCols) => {
    setCols(newCols)
  }, [])

  useEffect(() => {
    bitsFetch({}, 'log/get').then((response) => {
      if (response?.success && Array.isArray(response.data)) {
        setTableData(response.data)
      }
      setLoading(false)
    })
  }, [])

  const handleRowClick = useCallback((e, cells) => {
    const row = cells?.[0]?.row?.original
    if (row) setSelectedLog(row)
  }, [])

  const handleDelete = (selectedRowIds) => {
    const ids = selectedRowIds.map((item) => item.original.id)
    bitsFetch({ ids }, 'log/delete').then((response) => {
      if (response?.success) {
        setTableData((prev) => prev.filter((row) => !ids.includes(row.id)))
        setSelectedLog((prev) => (prev && ids.includes(prev.id) ? null : prev))
        setSnackbar({ show: true, msg: __('Successfully deleted', 'bitwelzp') })
      }
    })
  }

  const handleClearAll = () => {
    bitsFetch({}, 'log/clear').then((response) => {
      if (response?.success) {
        setTableData([])
        setSelectedLog(null)
        setSnackbar({ show: true, msg: __('All logs cleared', 'bitwelzp') })
      }
      setShowClearModal(false)
    })
  }

  if (loading) return <TableLoader />

  return (
    <div id="all-logs">
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <div className="forms">
        <Table
          className="f-table btcd-all-frm"
          height={500}
          columns={cols}
          data={tableData}
          setTableData={setTableData}
          rowSeletable
          resizable
          columnHidable
          setTableCols={setTableCols}
          search
          tableName="logs"
          DataFetchBtn={tableData.length > 0 && (
            <button
              type="button"
              className="btn btcd-btn-o-gray br-50"
              style={{ marginLeft: 12 }}
              onClick={() => setShowClearModal(true)}
            >
              {__('Clear All Logs', 'bitwelzp')}
            </button>
          )}
          handleDelete={handleDelete}
          rowClickable
          onRowClick={handleRowClick}
        />
      </div>

      <LogDetailsDrawer log={selectedLog} close={() => setSelectedLog(null)} />

      <ConfirmModal
        show={showClearModal}
        close={() => setShowClearModal(false)}
        action={handleClearAll}
        btnTxt={__('Clear All', 'bitwelzp')}
        title={__('Clear All Logs', 'bitwelzp')}
        body={__('Are you sure? Every log entry will be permanently deleted.', 'bitwelzp')}
      />
    </div>
  )
}

export default memo(Logs)
