import { __, sprintf } from '../../Utils/i18nwrap'
import bitsFetch from '../../Utils/bitsFetch'

export const handleAuthorize = (confTmp, setConf, setError, setisAuthorized, setisLoading, setSnackbar) => {
    console.log(confTmp)
    if (!confTmp.integ_config.auth_details.dataCenter || !confTmp.integ_config.auth_details.clientId || !confTmp.integ_config.auth_details.clientSecret) {
        setError({
            dataCenter: !confTmp.integ_config.auth_details.dataCenter ? __('Data center cann\'t be empty', 'bitwelzp') : '',
            clientId: !confTmp.integ_config.auth_details.clientId ? __('Client ID cann\'t be empty', 'bitwelzp') : '',
            clientSecret: !confTmp.integ_config.auth_details.clientSecret ? __('Secret key cann\'t be empty', 'bitwelzp') : '',
        })
        return
    }

    setisLoading(true)
    const scopes = 'ZOHOPEOPLE.forms.ALL'
    const apiEndpoint = `https://accounts.zoho.${confTmp.integ_config.auth_details.dataCenter}/oauth/v2/auth?scope=${scopes}&response_type=code&client_id=${confTmp.integ_config.auth_details.clientId}&prompt=Consent&access_type=offline&redirect_uri=${bitwelzp?.redirect}&state=${encodeURIComponent(window.location.href)}/redirect`
    const authWindow = window.open(apiEndpoint, 'zohoPeople', 'width=400,height=609,toolbar=off')
    const popupURLCheckTimer = setInterval(() => {
        if (authWindow.closed) {
            clearInterval(popupURLCheckTimer)
            let grantTokenResponse = {}
            let isauthRedirectLocation = false
            const zohoPeople = localStorage.getItem('zohoPeople')

            if (zohoPeople) {
                isauthRedirectLocation = true
                grantTokenResponse = JSON.parse(zohoPeople)
                localStorage.removeItem('zohoPeople')
            }
            if (!grantTokenResponse.code || grantTokenResponse.error || !grantTokenResponse || !isauthRedirectLocation) {
                const errorCause = grantTokenResponse.error ? `Cause: ${grantTokenResponse.error}` : ''
                setSnackbar({ show: true, msg: `${__('Authorization failed', 'bitwelzp')} ${errorCause}. ${__('please try again', 'bitwelzp')}` })
                setisLoading(false)
            } else {
                const newConf = { ...confTmp }
                newConf.accountServer = grantTokenResponse['accounts-server']
                tokenHelper(grantTokenResponse, newConf, setConf, setisAuthorized, setisLoading, setSnackbar)
            }
        }
    }, 500)
}

const tokenHelper = (grantToken, confTmp, setConf, setisAuthorized, setisLoading, setSnackbar) => {
    const tokenRequestParams = { ...grantToken }
    tokenRequestParams.dataCenter = confTmp.integ_config.auth_details.dataCenter
    tokenRequestParams.clientId = confTmp.integ_config.auth_details.clientId
    tokenRequestParams.clientSecret = confTmp.integ_config.auth_details.clientSecret
    tokenRequestParams.redirectURI = bitwelzp?.redirect

    bitsFetch(tokenRequestParams, 'generate_token')
        .then(result => result)
        .then(result => {
            if (result && result.success) {
                const newConf = { ...confTmp }
                newConf.integ_config.auth_details.tokenDetails = result.data
                newConf.integ_config.auth_details.isAuthorized = true
                setConf(newConf)
                setisAuthorized(true)
                setSnackbar({ show: true, msg: __('Authorized Successfully', 'bitwelzp') })
            } else if ((result && result.data && result.data.data) || (!result.success && typeof result.data === 'string')) {
                setSnackbar({ show: true, msg: `${__('Authorization failed Cause:', 'bitwelzp')}${result.data.data || result.data}. ${__('please try again', 'bitwelzp')}` })
            } else {
                setSnackbar({ show: true, msg: __('Authorization failed. please try again', 'bitwelzp') })
            }
            setisLoading(false)
        })
}