/* eslint-disable no-param-reassign */
export default function MinMaxLength(props) {
  const updateMinLength = e => {
    e.preventDefault()
    props.elm.data.child.map(node => {
      if (node.tag === 'input') {
        node.attr.minLength = e.target.value
        if (e.target.value.trim() === '') {
          delete node.attr.minLength
        }
      }
      return null
    })
    props.updateData(props.elm)
  }

  const updateMaxLength = e => {
    e.preventDefault()
    props.elm.data.child.map(node => {
      if (node.tag === 'input') {
        node.attr.maxLength = e.target.value
        if (e.target.value.trim() === '') {
          delete node.attr.maxLength
        }
      }
      return null
    })
    props.updateData(props.elm)
  }

  return (
    <div className="mt-3">
      <div className="flx">
        <div className="setting-inp ml-2">
          <span>Min Length:</span>
          <input style={{ width: '70%' }} type="number" onChange={updateMinLength} value={props.minLength} />
        </div>
        <div className="setting-inp">
          <span>Max Length:</span>
          <input style={{ width: '70%' }} type="number" onChange={updateMaxLength} value={props.maxLength} />
        </div>
      </div>

    </div>
  )
}
