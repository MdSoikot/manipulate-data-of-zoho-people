import { useState, useEffect } from 'react'
import { __ } from '../Utils/i18nwrap'
import CopyText from '../components/Utilities/CopyText'
import LoaderSm from '../components/Loaders/LoaderSm'
import { handleAuthorize } from '../components/ZohoPeople/ZohoPeopleCommonFunc'
import { setGrantTokenResponse } from '../components/ZohoPeople/IntegrationHelpers'
import SnackMsg from '../components/Utilities/SnackMsg'
import bitsFetch from '../Utils/bitsFetch'
import { $integrationDetails } from '../Utils/GlobalStates'
import { useRecoilState } from 'recoil'

export default function Authorization() {
    const [integConfig, setIntegConfig] = useRecoilState($integrationDetails)
    const [snack, setSnackbar] = useState({ show: false })
    const [isLoading, setisLoading] = useState(false)
    const [isSaveLoading, setIsSaveLoading] = useState(false)
    const [error, setError] = useState({ dataCenter: '', clientId: '', clientSecret: '' })
    const [isAuthorized, setisAuthorized] = useState(false)
    const [isSaved, setIsSaved] = useState(integConfig?.integ_config?.auth_details?.isAuthorized)

    useEffect(() => {
        window.opener && setGrantTokenResponse('zohoPeople')
    }, [])


    const handleInput = (e) => {
        const newConfig = { ...integConfig }
        const rmError = { ...error }
        rmError[e.target.name] = ''
        newConfig.integ_config.auth_details[e.target.name] = e.target.value
        setError(rmError)
        setIntegConfig(newConfig)
    }

    const handleSave = () => {
        setIsSaveLoading(true)
        if (isSaved) {
            integConfig.integ_config.auth_details.integrationId = integConfig?.integ_config?.integration_details?.id
            bitsFetch(integConfig.integ_config.auth_details, 'integration_update', { method: 'POST' })
                .then(response => {
                    setIsSaveLoading(false)
                    console.log(response)
                    if (response.success) {
                        setSnackbar({ show: true, msg: __('Integration Updated Successfully', 'bitwelzp') })
                    } else {
                        setSnackbar({ show: true, msg: __('Nothing Update', 'bitwelzp') })
                    }
                })
        } else {
            bitsFetch(integConfig.integ_config.auth_details, 'integration_save')
                .then(response => {
                    setIsSaveLoading(false)
                    if (response.success) {
                        setSnackbar({ show: true, msg: __('Integration Saved Successfully', 'bitwelzp') })
                        setIsSaved(true)
                    } else {
                        setSnackbar({ show: true, msg: __('Saving Failed', 'bitwelzp') })
                    }
                })
        }

    }

    return (
        <div className="btcd-stp-page" style={{ ...{ width: 900 }, ...{ height: `${100}%` }, 'padding': '40px' }}>
            <div className="mt-3"><b>{__('Data Center:', 'bitwelzp')}</b></div>
            <select name="dataCenter" onChange={handleInput} value={integConfig.integ_config.auth_details.dataCenter} className="btcd-paper-inp w-9 mt-1">
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
            <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientId" value={integConfig.integ_config.auth_details.clientId} type="text" placeholder={__('Client id...', 'bitwelzp')} />
            <div style={{ color: 'red' }}>{error.clientId}</div>

            <div className="mt-3"><b>{__('Client secret:', 'bitwelzp')}</b></div>
            <input className="btcd-paper-inp w-6 mt-1" onChange={handleInput} name="clientSecret" value={integConfig.integ_config.auth_details.clientSecret} type="text" placeholder={__('Client secret...', 'bitwelzp')} />
            <div style={{ color: 'red' }}>{error.clientSecret}</div>


            <button onClick={() => handleAuthorize(integConfig, setIntegConfig, setError, setisAuthorized, setisLoading, setSnackbar)} className="btn btcd-btn-lg green sh-sm flx" type="button">
                {isSaved ? __('Authorized ✔', 'bitwelzp') : __('Authorize', 'bitwelzp')}
                {isLoading && <LoaderSm size="20" clr="#022217" className="ml-2" />}
            </button>
            <br />
            <button className="btn f-right btcd-btn-lg green sh-sm flx" type="button" disabled={!integConfig.integ_config.auth_details.isAuthorized} onClick={handleSave}>
                {isSaved ? __('Update', 'bitwelzp') : __('Save', 'bitwelzp')}
                {isSaveLoading && <LoaderSm size="20" clr="#022217" className="ml-2" />}
            </button>
            <SnackMsg snack={snack} setSnackbar={setSnackbar} />

        </div>
    )
}






