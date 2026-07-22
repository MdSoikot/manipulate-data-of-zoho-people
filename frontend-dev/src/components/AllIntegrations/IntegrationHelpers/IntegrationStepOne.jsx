import { __ } from '../../../Utils/i18nwrap'
import CopyText from '../../Utilities/CopyText'
import LoaderSm from '../../Loaders/LoaderSm'

export default function IntegrationStepOne({ step, confTmp, handleInput, error, setSnackbar, handleAuthorize, isLoading, isAuthorized, nextPage, children }) {
  return (
    <div className="btcd-stp-page" style={{ ...{ width: step === 1 && 900 }, ...{ height: step === 1 && `${100}%` } }}>
      <div className="mt-3"><b>{__('Integration Name:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="name" value={confTmp.name} type="text" placeholder={__('Integration Name...', 'bitwelzp')} />

      <div className="mt-3"><b>{__('Data Center:', 'bitwelzp')}</b></div>
      <select onChange={handleInput} name="dataCenter" value={confTmp.dataCenter} className="btcd-paper-inp w-9 mt-1">
        <option value="">{__('--Select a data center--', 'bitwelzp')}</option>
        <option value="com">zoho.com</option>
        <option value="eu">zoho.eu</option>
        <option value="com.cn">zoho.com.cn</option>               
        <option value="in">zoho.in</option>
        <option value="com.au">zoho.com.au</option>
      </select>
      <div style={{ color: 'red' }}>{error.dataCenter}</div>

      <div className="mt-3"><b>{__('Homepage URL:', 'bitwelzp')}</b></div>
      <CopyText value={`${window.location.origin}`} setSnackbar={setSnackbar} className="field-key-cpy w-6 ml-0" />

      <div className="mt-3"><b>{__('Authorized Redirect URIs:', 'bitwelzp')}</b></div>
      <CopyText value={`${window.location.href}/redirect`} setSnackbar={setSnackbar} className="field-key-cpy w-6 ml-0" />

      <small className="d-blk mt-5">
        {__('To get Client ID and SECRET , Please Visit', 'bitwelzp')}
        {' '}
        <a className="btcd-link" href="https://api-console.zoho.com/" target="_blank" rel="noreferrer">{__('Zoho API Console', 'bitwelzp')}</a>
      </small>

      <div className="mt-3"><b>{__('Client id:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientId" value={confTmp.clientId} type="text" placeholder={__('Client id...', 'bitwelzp')} />
      <div style={{ color: 'red' }}>{error.clientId}</div>

      <div className="mt-3"><b>{__('Client secret:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientSecret" value={confTmp.clientSecret} type="text" placeholder={__('Client secret...', 'bitwelzp')} />
      <div style={{ color: 'red' }}>{error.clientSecret}</div>

      {children}

      <button onClick={handleAuthorize} className="btn btcd-btn-lg green sh-sm flx" type="button" disabled={isAuthorized}>
        {isAuthorized ? __('Authorized ✔', 'bitwelzp') : __('Authorize', 'bitwelzp')}
        {isLoading && <LoaderSm size="20" clr="#022217" className="ml-2" />}
      </button>
      <br />
      <button onClick={() => nextPage(2)} className="btn f-right btcd-btn-lg green sh-sm flx" type="button" disabled={!isAuthorized}>
        {__('Next', 'bitwelzp')}
        {' '}
&nbsp;
        <div className="btcd-icn icn-arrow_back rev-icn d-in-b" />
      </button>
    </div>
  )
}
