/* eslint-disable no-nested-ternary */
/* eslint-disable react/jsx-props-no-spreading */

import { memo, useEffect, useState, useRef, forwardRef } from 'react'
import { Scrollbars } from 'react-custom-scrollbars'
import { ReactSortable } from 'react-sortablejs'
import { useColumnOrder, useFilters, useFlexLayout, useGlobalFilter, usePagination, useResizeColumns, useRowSelect, useSortBy, useTable } from 'react-table'
import { useSticky } from 'react-table-sticky'
import { __ } from '../../Utils/i18nwrap'
import bitsFetch from '../../Utils/bitsFetch'
import { colId, savedHiddenIds } from '../../Utils/tableColPersist'
import ConfirmModal from './ConfirmModal'
import Menu from './Menu'
import TableCheckBox from './TableCheckBox'
import TableLoader2 from '../Loaders/TableLoader2'

const IndeterminateCheckbox = forwardRef(
  ({ indeterminate, ...rest }, ref) => {
    const defaultRef = useRef()
    const resolvedRef = ref || defaultRef
    useEffect(() => {
      resolvedRef.current.indeterminate = indeterminate
    }, [resolvedRef, indeterminate])
    return <TableCheckBox refer={resolvedRef} rest={rest} />
  },
)

function GlobalFilter({ globalFilter, setGlobalFilter, setSearch, exportImportMenu, data, cols, formID, report }) {
  const [delay, setDelay] = useState(null)
  const handleSearch = e => {
    delay && clearTimeout(delay)
    const { value } = e.target

    setGlobalFilter(value || undefined)

    setDelay(setTimeout(() => {
      setSearch(value || undefined)
    }, 1000))
  }

  return (
    <div className="f-search">
      <button type="button" className="icn-btn" aria-label="icon-btn" onClick={() => { setSearch(globalFilter || undefined) }}><span className="btcd-icn icn-search" /></button>
      {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
      <label>
        <input
          value={globalFilter || ''}
          onChange={handleSearch}
          placeholder={__('Search', 'bitwelzp')}
        />
      </label>
    </div>
  )
}

function ColumnHide({ cols, setCols, tableCol, tableAllCols }) {
  return (
    <Menu icn="icn-remove_red_eye">
      <Scrollbars autoHide style={{ width: 200 }}>
        <ReactSortable list={cols} setList={l => setCols(l)} handle=".btcd-pane-drg">
          {tableCol.map((column, i) => (
            <div key={tableAllCols[i + 1].id} className={`btcd-pane ${(column.Header === 'Actions' || typeof column.Header === 'object') && 'd-non'}`}>
              <TableCheckBox cls="scl-7" id={tableAllCols[i + 1].id} title={column.Header} rest={tableAllCols[i + 1].getToggleHiddenProps()} />
              <span className="btcd-pane-drg">&#8759;</span>
            </div>
          ))}
        </ReactSortable>
      </Scrollbars>
    </Menu>
  )
}

function Table(props) {
  const [confMdl, setconfMdl] = useState({ show: false, btnTxt: '' })
  const { columns, data, fetchData, report, handleDelete } = props
  const { getTableProps,
    getTableBodyProps,
    headerGroups,
    prepareRow,
    page,
    canPreviousPage,
    canNextPage,
    pageOptions,
    pageCount,
    gotoPage,
    nextPage,
    previousPage,
    setPageSize,
    state,
    preGlobalFilteredRows,
    selectedFlatRows, // row select
    allColumns, // col hide
    setGlobalFilter,
    state: { pageIndex, pageSize, sortBy, filters, globalFilter, hiddenColumns },
    setColumnOrder,
    setHiddenColumns } = useTable(
    {
      debug: true,
      fetchData,
      columns,
      data,
      manualPagination: typeof props.pageCount !== 'undefined',
      pageCount: props.pageCount,
      initialState: { hiddenColumns: savedHiddenIds(props.tableName) },
      autoResetPage: false,
      autoResetHiddenColumns: false,
      autoResetSortBy: false,
      autoResetFilters: false,
      autoResetGlobalFilter: false,
    },
    useFilters,
    useGlobalFilter,
    useSortBy,
    usePagination,
    useSticky,
    useColumnOrder,
    // useBlockLayout,
    useFlexLayout,
    props.resizable ? useResizeColumns : '', // resize
    props.rowSeletable ? useRowSelect : '', // row select
    props.rowSeletable ? (hooks => {
      hooks.allColumns.push(cols => [
        {
          id: 'selection',
          width: 50,
          maxWidth: 50,
          minWidth: 67,
          sticky: 'left',
          Header: ({ getToggleAllPageRowsSelectedProps }) => <IndeterminateCheckbox {...getToggleAllPageRowsSelectedProps()} />,
          Cell: ({ row }) => <IndeterminateCheckbox {...row.getToggleRowSelectedProps()} />,
        },
        ...cols,
      ])
    }) : '',
  )

  const [search, setSearch] = useState(globalFilter)

  // persist column order & visibility; first run only records the baseline
  const lastSavedCols = useRef(null)
  useEffect(() => {
    if (!props.tableName) return undefined
    const payload = columns
      .filter(c => colId(c) && colId(c) !== 't_action')
      .map(c => ({ id: colId(c), hidden: !!hiddenColumns?.includes(colId(c)) }))
    const serialized = JSON.stringify(payload)
    if (lastSavedCols.current === null) {
      lastSavedCols.current = serialized
      return undefined
    }
    if (serialized === lastSavedCols.current) return undefined
    const timer = setTimeout(() => {
      lastSavedCols.current = serialized
      bitsFetch({ table: props.tableName, columns: payload }, 'save_table_columns')
    }, 800)
    return () => clearTimeout(timer)
  }, [columns, hiddenColumns, props.tableName])

  useEffect(() => {
    if (fetchData) {
      fetchData({ pageIndex, pageSize })
    }
  }, [fetchData, pageIndex, pageSize])
  useEffect(() => {
    if (pageIndex > pageCount) {
      gotoPage(0)
    }
  }, [gotoPage, pageCount, pageIndex])

  const showBulkDupMdl = () => {
    confMdl.action = () => { props.duplicateData(selectedFlatRows, data, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter: search } }); closeConfMdl() }
    confMdl.btnTxt = __('Duplicate', 'bitwelzp')
    confMdl.btn2Txt = null
    confMdl.btnClass = 'blue'
    confMdl.body = `${__('Do You want Deplicate these', 'bitwelzp')} ${selectedFlatRows.length} ${__('item', 'bitwelzp')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const showStModal = () => {
    confMdl.action = (e) => { props.setBulkStatus(e, selectedFlatRows); closeConfMdl() }
    confMdl.btn2Action = (e) => { props.setBulkStatus(e, selectedFlatRows); closeConfMdl() }
    confMdl.btnTxt = __('Disable', 'bitwelzp')
    confMdl.btn2Txt = __('Enable', 'bitwelzp')
    confMdl.body = `${__('Do you want to change these', 'bitwelzp')} ${selectedFlatRows.length} ${__('status', 'bitwelzp')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const showDelModal = () => {
    confMdl.action = () => { handleDelete(selectedFlatRows); closeConfMdl() }
    confMdl.btnTxt = __('Delete', 'bitwelzp')
    confMdl.btn2Txt = null
    confMdl.btnClass = ''
    confMdl.body = `${__('Are you sure to delete these', 'bitwelzp')} ${selectedFlatRows.length} ${__('items', 'bitwelzp')} ?`
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const closeConfMdl = () => {
    confMdl.show = false
    setconfMdl({ ...confMdl })
  }

  return (
    <>
      <ConfirmModal
        show={confMdl.show}
        body={confMdl.body}
        action={confMdl.action}
        close={closeConfMdl}
        btnTxt={confMdl.btnTxt}
        btn2Txt={confMdl.btn2Txt}
        btn2Action={confMdl.btn2Action}
        btnClass={confMdl.btnClass}
      />
      <div className="btcd-t-actions">
        <div className="flx">

          {props.columnHidable && <ColumnHide cols={props.columns} setCols={props.setTableCols} tableCol={columns} tableAllCols={allColumns} />}
          {props.DataFetchBtn}

          {props.rowSeletable && selectedFlatRows.length > 0
            && (
              <>
                {'setBulkStatus' in props
                  && (
                    <button onClick={showStModal} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Status"' }} aria-label="icon-btn" type="button">
                      <span className="btcd-icn icn-toggle_off" />
                    </button>
                  )}
                {'duplicateData' in props
                  && (
                    <button onClick={showBulkDupMdl} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Duplicate"' }} aria-label="icon-btn" type="button">
                      <span className="btcd-icn icn-file_copy" style={{ fontSize: 16 }} />
                    </button>
                  )}
                <button onClick={showDelModal} className="icn-btn btcd-icn-lg tooltip" style={{ '--tooltip-txt': '"Delete"' }} aria-label="icon-btn" type="button">
                  <span className="btcd-icn icn-trash-fill" style={{ fontSize: 16 }} />
                </button>
                <small className="btcd-pill">
                  {selectedFlatRows.length}
                  {' '}
                  {__('Row Selected', 'bitwelzp')}
                </small>
              </>
            )}
        </div>
      </div>
      <>
        {props.search
          && (
            <GlobalFilter
              preGlobalFilteredRows={preGlobalFilteredRows}
              globalFilter={state.globalFilter}
              setGlobalFilter={setGlobalFilter}
              setSearch={setSearch}
              exportImportMenu={props.exportImportMenu}
              data={props.data}
              cols={props.columns}
              formID={props.formID}
              report={report}
            />
          )}

        <div className="mt-2">
          <Scrollbars className="btcd-scroll" style={{ height: props.height }}>
            <div {...getTableProps()} className={`${props.className} ${props.rowClickable && 'rowClickable'}`}>
              <div className="thead">
                {headerGroups.map(headerGroup => {
                  const { key: headerGroupKey, ...headerGroupProps } = headerGroup.getHeaderGroupProps()
                  return (
                    <div key={headerGroupKey} className="tr" {...headerGroupProps}>
                      {headerGroup.headers.map(column => {
                        const { key: headerKey, ...headerProps } = column.getHeaderProps()
                        return (
                          <div key={headerKey} className="th flx" {...headerProps}>
                            <div {...column.id !== 't_action' && column.getSortByToggleProps()}>
                              {column.render('Header')}
                              {' '}
                              {(column.id !== 't_action' && column.id !== 'selection') && (
                                <span>
                                  {column.isSorted
                                    ? column.isSortedDesc
                                      ? String.fromCharCode(9662)
                                      : String.fromCharCode(9652)
                                    : <span className="btcd-icn icn-sort" style={{ fontSize: 10, marginLeft: 5 }} />}
                                </span>
                              )}
                            </div>
                            {props.resizable
                              && (
                                <div
                                  {...column.getResizerProps()}
                                  className={`btcd-t-resizer ${column.isResizing ? 'isResizing' : ''}`}
                                />
                              )}
                          </div>
                        )
                      })}
                    </div>
                  )
                })}
              </div>
              {props.loading ? <TableLoader2 /> : (
                <div className="tbody" {...getTableBodyProps()}>
                  {page.map(row => {
                    prepareRow(row)
                    const { key: rowKey, ...rowProps } = row.getRowProps()
                    return (
                      <div
                        key={rowKey}
                        className={`tr ${row.isSelected ? 'btcd-row-selected' : ''}`}
                        {...rowProps}
                      >
                        {row.cells.map(cell => {
                          const { key: cellKey, ...cellProps } = cell.getCellProps()
                          return (
                            <div
                              key={cellKey}
                              className="td flx"
                              {...cellProps}
                              onClick={(e) => props.rowClickable && typeof cell.column.Header === 'string' && props.onRowClick(e, row.cells, cell.row.index, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter } })}
                              onKeyPress={(e) => props.rowClickable && typeof cell.column.Header === 'string' && props.onRowClick(e, row.cells, cell.row.index, { fetchData, data: { pageIndex, pageSize, sortBy, filters, globalFilter } })}
                              role="button"
                              tabIndex={0}
                              aria-label="cell"
                            >
                              {cell.render('Cell')}
                            </div>
                          )
                        })}
                      </div>
                    )
                  })}
                </div>
              )}
            </div>
          </Scrollbars>
        </div>
      </>

      <div className="btcd-pagination">
        <small>
          {props.countEntries >= 0 && (
            `${__('Total Response:', 'bitwelzp')}
            ${props.countEntries}`
          )}
        </small>
        <div>
          <button aria-label="Go first" className="icn-btn" type="button" onClick={() => gotoPage(0)} disabled={!canPreviousPage}>
            &laquo;
          </button>
          {' '}
          <button aria-label="Back" className="icn-btn" type="button" onClick={() => previousPage()} disabled={!canPreviousPage}>
            &lsaquo;
          </button>
          {' '}
          <button aria-label="Next" className="icn-btn" type="button" onClick={() => nextPage()} disabled={!canNextPage}>
            &rsaquo;
          </button>
          {' '}
          <button aria-label="Last" className="icn-btn" type="button" onClick={() => gotoPage(pageCount - 1)} disabled={!canNextPage}>
            &raquo;
          </button>
          {' '}
          <small>
            &nbsp;
            {__('Page', 'bitwelzp')}
            {' '}
            <strong>
              {pageIndex + 1}
              {' '}
              {__('of', 'bitwelzp')}
              {' '}
              {pageOptions.length}
              {' '}
              &nbsp;
            </strong>
            {' '}
          </small>
          {/* eslint-disable-next-line jsx-a11y/label-has-associated-control */}
          <label>
            <select
              className="btcd-paper-inp"
              value={pageSize}
              onChange={e => {
                setPageSize(Number(e.target.value))
                if (props.getPageSize) {
                  props.getPageSize(e.target.value, pageIndex)
                }
              }}
            >
              {[10, 20, 50, 100].map(pageSiz => (
                <option key={pageSiz} value={pageSiz}>
                  {__('Show', 'bitwelzp')}
                  {' '}
                  {pageSiz}
                </option>
              ))}
            </select>
          </label>
        </div>
      </div>

    </>
  )
}

export default memo(Table)
