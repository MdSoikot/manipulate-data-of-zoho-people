import { useState } from 'react'
import { __ } from '../../../Utils/i18nwrap'
import CopyText from '../../Utilities/CopyText'
import LoaderSm from '../../Loaders/LoaderSm'
import { handleAuthorize, refreshModules } from './ZohoCRMCommonFunc'

export default function ZohoCRMAuthorization({ formID, crmConf, setCrmConf, step, setstep, isLoading, setisLoading, setSnackbar, redirectLocation, isInfo }) {
  const [isAuthorized, setisAuthorized] = useState(false)
  const [error, setError] = useState({ dataCenter: '', clientId: '', clientSecret: '' })
  const nextPage = () => {
    setstep(2)
    !crmConf.module && refreshModules(formID, crmConf, setCrmConf, setisLoading, setSnackbar)
    document.querySelector('.btcd-s-wrp').scrollTop = 0
  }
  const handleInput = e => {
    const newConf = { ...crmConf }
    const rmError = { ...error }
    rmError[e.target.name] = ''
    newConf[e.target.name] = e.target.value
    setError(rmError)
    setCrmConf(newConf)
  }

  return (
    <div className="btcd-stp-page" style={{ ...{ width: step === 1 && 900 }, ...{ height: step === 1 && `${100}%` } }}>
      <div className="mt-3"><b>{__('Integration Name:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="name" value={crmConf.name} type="text" placeholder={__('Integration Name...', 'bitwelzp')} disabled={isInfo} />

      <div className="mt-3"><b>{__('Data Center:', 'bitwelzp')}</b></div>
      <select onChange={handleInput} name="dataCenter" value={crmConf.dataCenter} className="btcd-paper-inp w-9 mt-1" disabled={isInfo}>
        <option value="">{__('--Select a data center--', 'bitwelzp')}</option>
        <option value="com">zoho.com</option>
        <option value="eu">zoho.eu</option>
        <option value="com.cn">zoho.com.cn</option>
        <option value="in">zoho.in</option>
        <option value="com.au">zoho.com.au</option>
      </select>
      <div style={{ color: 'red' }}>{error.dataCenter}</div>

      <div className="mt-3"><b>{__('Homepage URL:', 'bitwelzp')}</b></div>
      <CopyText value={`${window.location.origin}`} setSnackbar={setSnackbar} className="field-key-cpy w-6 ml-0" readOnly={isInfo} />

      <div className="mt-3"><b>{__('Authorized Redirect URIs:', 'bitwelzp')}</b></div>
      <CopyText value={redirectLocation || `${window.location.href}/redirect`} setSnackbar={setSnackbar} className="field-key-cpy w-6 ml-0" readOnly={isInfo} />

      <small className="d-blk mt-5">
        {__('To get Client ID and SECRET , Please Visit', 'bitwelzp')}
        {' '}
        <a className="btcd-link" href={`https://api-console.zoho.${crmConf?.dataCenter || 'com'}/`} target="_blank" rel="noreferrer">{__('Zoho API Console', 'bitwelzp')}</a>
      </small>

      <div className="mt-3"><b>{__('Client id:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientId" value={crmConf.clientId} type="text" placeholder={__('Client id...', 'bitwelzp')} disabled={isInfo} />
      <div style={{ color: 'red' }}>{error.clientId}</div>

      <div className="mt-3"><b>{__('Client secret:', 'bitwelzp')}</b></div>
      <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientSecret" value={crmConf.clientSecret} type="text" placeholder={__('Client secret...', 'bitwelzp')} disabled={isInfo} />
      <div style={{ color: 'red' }}>{error.clientSecret}</div>

      {!isInfo && (
        <>
          <button onClick={() => handleAuthorize(crmConf, setCrmConf, setError, setisAuthorized, setisLoading, setSnackbar)} className="btn btcd-btn-lg green sh-sm flx" type="button" disabled={isAuthorized}>
            {isAuthorized ? __('Authorized ✔', 'bitwelzp') : __('Authorize', 'bitwelzp')}
            {isLoading && <LoaderSm size="20" clr="#022217" className="ml-2" />}
          </button>
          <br />
          <button onClick={nextPage} className="btn f-right btcd-btn-lg green sh-sm flx" type="button" disabled={!isAuthorized}>
            {__('Next', 'bitwelzp')}
            <div className="btcd-icn icn-arrow_back rev-icn d-in-b" />
          </button>
        </>
      )}
    </div>
  )
}
