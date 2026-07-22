export default function Progressbar({ value }) {
  return (
    <div className="flx" style={{ width: '100%' }}>
      <span style={{ minWidth: 68 }}>
        {value}
        %
      </span>
      <div className="btcd-prgrs-wrp">
        <div className="btcd-prgrs" style={{ width: `${value}%` }} />
      </div>
    </div>
  )
}
