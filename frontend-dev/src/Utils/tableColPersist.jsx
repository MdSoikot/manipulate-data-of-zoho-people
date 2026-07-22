/* eslint-disable no-undef */

// stable id for a column def (function accessors carry an explicit `id`)
export const colId = (col) => col.id || (typeof col.accessor === 'string' ? col.accessor : undefined)

export const getSavedCols = (tableName) => {
  if (typeof bitwelzp === 'undefined' || !tableName) return null
  const saved = bitwelzp.table_columns?.[tableName]
  return Array.isArray(saved) && saved.length ? saved : null
}

export const savedHiddenIds = (tableName) => (getSavedCols(tableName) || [])
  .filter((c) => c.hidden)
  .map((c) => c.id)

// apply saved order to code-defined columns; columns unknown to the saved
// state (newly added in code) keep their relative order, appended at the end
export const mergeSavedCols = (tableName, cols) => {
  const saved = getSavedCols(tableName)
  if (!saved) return cols
  const savedIds = saved.map((c) => c.id)
  const known = []
  const fresh = []
  cols.forEach((col) => {
    const idx = savedIds.indexOf(colId(col))
    if (idx === -1) fresh.push(col)
    else known[idx] = col
  })
  return [...known.filter(Boolean), ...fresh]
}
