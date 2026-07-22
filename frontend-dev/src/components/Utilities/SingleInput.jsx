export default function SingleInput({ className, width, title, inpType, action, value, placeholder, name }) {
  return (
    <div className={`mt-3 setting-inp ${className}`} style={{ ...(width && { width }) }}>
      <span>{title}</span>
      <input className="btcd-paper-inp" type={inpType} onChange={action} value={value} placeholder={placeholder} name={name} />
    </div>
  )
}
