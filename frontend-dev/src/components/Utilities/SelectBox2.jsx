export default function SelectBox2({ title, value, action, name, options }) {
  return (
    <div className="mt-3 setting-inp">
      <span>{title}</span>
      <select className="btcd-paper-inp mt-1" value={value} onChange={action} name={name}>
        {options.map(itm => <option key={`btcd-k-${itm.name}`} value={itm.value}>{itm.name}</option>)}
      </select>
    </div>
  )
}
