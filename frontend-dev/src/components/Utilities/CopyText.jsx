import { __ } from '../../Utils/i18nwrap'

export default function CopyText({ value, setSnackbar, className, readOnly }) {
  const copyText = e => {
    const cpyBtn = e.target
    cpyBtn.setAttribute('style', '--tooltip-txt: "Copied"')
    setSnackbar({ show: true, msg: __('Copied on Clipboard.', 'bitwelzp') })
    const text = e.target.parentNode.children[0]
    text.select()
    text.setSelectionRange(0, 99999)
    document.execCommand('copy')
    setTimeout(() => { cpyBtn.setAttribute('style', '--tooltip-txt: "Copy"') }, 2000)
  }

  return (
    <div className={className}>
      <label htmlFor={value} className="flx">
        <input className={`w-10 ${readOnly && 'readonly'}`} value={value} readOnly />
        <button onClick={copyText} className="tooltip" style={{ '--tooltip-txt': '"Copy"' }} aria-label="Copy" type="button"><span className="btcd-icn icn-copy" /></button>
      </label>
    </div>
  )
}
