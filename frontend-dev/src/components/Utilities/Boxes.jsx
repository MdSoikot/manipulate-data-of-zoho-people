/* eslint-disable jsx-a11y/label-has-associated-control */
export default function Boxes(props) {
  return (
    <div className="flx flx-between">
      <input className="input" type="text" onChange={(e) => props.updateOption(e, props.idx)} value={props.itm.child[0].child} />
      <div className="flx">
        <label className="btcd-ck-wrp">
          <input type="checkbox" checked={props.isChecked} onChange={(e) => props.setCheck(e, props.idx)} />
          <span className="btcd-mrk ck br-50" />
        </label>
        <button className="btn cls-btn" type="button" onClick={() => props.delOption(props.idx)}>&times;</button>
      </div>

    </div>
  )
}
