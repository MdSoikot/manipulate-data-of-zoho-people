import { __, sprintf } from '../../../Utils/i18nwrap'
import bitsFetch from '../../../Utils/bitsFetch'

export const handleInput = (e, recordTab, crmConf, setCrmConf, formID, setisLoading, setSnackbar, isNew, error, setError) => {
  let newConf = { ...crmConf }
  if (recordTab === 0) {
    if (isNew) {
      const rmError = { ...error }
      rmError[e.target.name] = ''
      setError({ ...rmError })
    }
    newConf[e.target.name] = e.target.value
  } else {
    if (!newConf.relatedlists) {
      newConf.relatedlists = []
    }
    newConf.relatedlists[recordTab - 1][e.target.name] = e.target.value
  }

  switch (e.target.name) {
    case 'module':
      newConf = moduleChange(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)
      break
    case 'layout':
      newConf = layoutChange(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)
      break
    default:
      break
  }
  setCrmConf({ ...newConf })
}

export const handleTabChange = (recordTab, settab, formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  if (recordTab) {
    !crmConf.default?.relatedlists?.[crmConf.module] && refreshRelatedList(formID, crmConf, setCrmConf, setisLoading, setSnackbar)
  }
  settab(recordTab)
}

export const moduleChange = (recordTab, formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  let newConf = { ...crmConf }
  const module = recordTab === 0 ? newConf.module : newConf.relatedlists[recordTab - 1].module
  if (!newConf.relatedlists[recordTab - 1]) newConf.relatedlists[recordTab - 1] = {}

  if (recordTab === 0) {
    newConf.layout = ''
    newConf.actions = {}
    newConf.field_map = [{ formField: '', zohoFormField: '' }]
    newConf.upload_field_map = [{ formField: '', zohoFormField: '' }]

    newConf.relatedlists = []
  } else {
    newConf.relatedlists[recordTab - 1].layout = ''
    newConf.relatedlists[recordTab - 1].actions = {}
    newConf.relatedlists[recordTab - 1].field_map = [{ formField: '', zohoFormField: '' }]
    newConf.relatedlists[recordTab - 1].upload_field_map = [{ formField: '', zohoFormField: '' }]
  }

  if (!newConf.default.layouts?.[module]) {
    refreshLayouts(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)
  } else {
    const layouts = Object.keys(newConf.default.layouts?.[module])
    if (layouts.length === 1) {
      if (recordTab === 0) { [newConf.layout] = layouts } else { [newConf.relatedlists[recordTab - 1].layout] = layouts }
      newConf = layoutChange(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)
    }
  }

  return newConf
}

export const layoutChange = (recordTab, formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  const newConf = { ...crmConf }

  const module = recordTab === 0 ? newConf.module : newConf.relatedlists[recordTab - 1].module
  const layout = recordTab === 0 ? newConf.layout : newConf.relatedlists[recordTab - 1].layout

  if (recordTab === 0) {
    newConf.actions = {}

    newConf.field_map = newConf?.default?.layouts?.[module]?.[layout]?.required ? generateMappedField(recordTab, newConf) : [{ formField: '', zohoFormField: '' }]

    newConf.upload_field_map = (newConf?.default?.layouts?.[module]?.[layout]?.requiredFileUploadFields && Object.keys(newConf.default.layouts[module][layout].requiredFileUploadFields).length > 0) ? generateMappedField(recordTab, newConf, true) : [{ formField: '', zohoFormField: '' }]
  } else {
    newConf.relatedlists[recordTab - 1].actions = {}

    newConf.relatedlists[recordTab - 1].field_map = newConf?.default?.layouts?.[module]?.[layout]?.required ? generateMappedField(recordTab, newConf) : [{ formField: '', zohoFormField: '' }]

    newConf.relatedlists[recordTab - 1].upload_field_map = (newConf?.default?.layouts?.[module]?.[layout]?.requiredFileUploadFields && Object.keys(newConf.default.layouts[module][layout].requiredFileUploadFields).length > 0) ? generateMappedField(recordTab, newConf, true) : [{ formField: '', zohoFormField: '' }]
  }

  !newConf.default.tags?.[module] && refreshTags(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)

  return newConf
}

export const refreshModules = (formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  setisLoading(true)
  const refreshModulesRequestParams = {
    formID,
    id: crmConf.id,
    dataCenter: crmConf.dataCenter,
    clientId: crmConf.clientId,
    clientSecret: crmConf.clientSecret,
    tokenDetails: crmConf.tokenDetails,
  }
  bitsFetch(refreshModulesRequestParams, 'zcrm_refresh_modules')
    .then(result => {
      if (result && result.success) {
        const newConf = { ...crmConf }
        if (!newConf.default) newConf.default = {}
        if (result.data.modules) {
          newConf.default.modules = result.data.modules
        }
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('Modules refreshed', 'bitwelzp') })
      } else if ((result && result.data && result.data.data) || (!result.success && typeof result.data === 'string')) {
        setSnackbar({ show: true, msg: sprintf(__('Modules refresh failed Cause: %s. please try again', 'bitwelzp'), result.data.data || result.data) })
      } else {
        setSnackbar({ show: true, msg: __('Modules refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const refreshLayouts = (recordTab, formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  const newConf = { ...crmConf }
  const module = recordTab === 0 ? newConf.module : newConf.relatedlists[recordTab - 1].module
  if (!module) {
    return
  }
  setisLoading(true)
  const refreshLayoutsRequestParams = {
    formID,
    module,
    dataCenter: newConf.dataCenter,
    clientId: newConf.clientId,
    clientSecret: newConf.clientSecret,
    tokenDetails: newConf.tokenDetails,
  }
  bitsFetch(refreshLayoutsRequestParams, 'zcrm_refresh_layouts')
    .then(result => {
      if (result && result.success) {
        if (result.data.layouts) {
          if (!newConf.default.layouts) newConf.default.layouts = {}
          newConf.default.layouts[module] = result.data.layouts
          const layouts = [...Object.keys(result.data.layouts)]
          if (layouts.length === 1) {
            if (recordTab === 0) {
              [newConf.layout] = layouts
              newConf.field_map = generateMappedField(recordTab, newConf)
              if (Object.keys(result.data.layouts[layouts].fileUploadFields).length > 0) {
                newConf.upload_field_map = generateMappedField(recordTab, newConf, true)
              }
            } else {
              [newConf.relatedlists[recordTab - 1].layout] = layouts
              newConf.relatedlists[recordTab - 1].field_map = generateMappedField(recordTab, newConf)

              if (Object.keys(result.data.layouts[layouts].fileUploadFields).length > 0) {
                newConf.relatedlists[recordTab - 1].upload_field_map = generateMappedField(recordTab, newConf, true)
              }
            }

            if (!newConf.default.tags?.[module]) refreshTags(recordTab, formID, newConf, setCrmConf, setisLoading, setSnackbar)
          }
        }
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('Layouts refreshed', 'bitwelzp') })
      } else if ((result?.data?.data) || (!result.success && typeof result.data === 'string')) {
        setSnackbar({ show: true, msg: sprintf(__('Layouts refresh failed Cause: %s. please try again', 'bitwelzp'), result.data.data || result.data) })
      } else {
        setSnackbar({ show: true, msg: __('Layouts refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const refreshRelatedList = (formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  if (!crmConf.module) {
    return
  }
  setisLoading(true)
  const relatedListRequestParams = {
    formID,
    module: crmConf.module,
    dataCenter: crmConf.dataCenter,
    clientId: crmConf.clientId,
    clientSecret: crmConf.clientSecret,
    tokenDetails: crmConf.tokenDetails,
  }
  bitsFetch(relatedListRequestParams, 'zcrm_get_related_lists')
    .then(result => {
      if (result && result.success) {
        const newConf = { ...crmConf }
        if (result.data.relatedLists) {
          if (!newConf.default.relatedlists) {
            newConf.default.relatedlists = {}
          }
          newConf.default.relatedlists[newConf.module] = { ...result.data.relatedLists }
        }
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('RelatedLists refreshed', 'bitwelzp') })
      } else if ((result?.data?.data) || (!result.success && typeof result.data === 'string')) {
        setSnackbar({ show: true, msg: `${__('RelatedLists refresh failed Cause:')}${result.data.data || result.data}. ${__('please try again', 'bitwelzp')}` })
      } else {
        setSnackbar({ show: true, msg: __('RelatedLists refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const refreshTags = (recordTab, formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  const module = recordTab === 0 ? crmConf.module : crmConf.relatedlists[recordTab - 1].module
  if (!module) return
  setisLoading(true)
  const refreshTagsParams = {
    formID,
    module,
    dataCenter: crmConf.dataCenter,
    clientId: crmConf.clientId,
    clientSecret: crmConf.clientSecret,
    tokenDetails: crmConf.tokenDetails,
  }
  bitsFetch(refreshTagsParams, 'zcrm_get_tags')
    .then(result => {
      if (result?.success) {
        const newConf = { ...crmConf }
        if (result.data.tags) {
          if (!newConf.default.tags) {
            newConf.default.tags = {}
          }
          newConf.default.tags[module] = { ...result.data.tags }
        }
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('Tags refreshed', 'bitwelzp') })
      } else if ((result?.data?.data) || (!result.success && typeof result.data === 'string')) {
        setSnackbar({ show: true, msg: `${__('Tags refresh failed Cause:', 'bitwelzp')}${result.data.data || result.data}. ${__('please try again', 'bitwelzp')}` })
      } else {
        setSnackbar({ show: true, msg: __('Tags refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const refreshOwners = (formID, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  setisLoading(true)
  const getOwnersParams = {
    formID,
    dataCenter: crmConf.dataCenter,
    clientId: crmConf.clientId,
    clientSecret: crmConf.clientSecret,
    tokenDetails: crmConf.tokenDetails,
  }
  bitsFetch(getOwnersParams, 'zcrm_get_users')
    .then(result => {
      if (result?.success) {
        const newConf = { ...crmConf }
        newConf.default.crmOwner = result.data.users
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('Owners refreshed', 'bitwelzp') })
      } else {
        setSnackbar({ show: true, msg: __('Owners refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const refreshAssigmentRules = (recordTab, crmConf, setCrmConf, setisLoading, setSnackbar) => {
  const module = recordTab === 0 ? crmConf.module : crmConf.relatedlists[recordTab - 1].module
  if (!module) return
  setisLoading(true)
  const getAssigmentRulesParams = {
    module,
    dataCenter: crmConf.dataCenter,
    clientId: crmConf.clientId,
    clientSecret: crmConf.clientSecret,
    tokenDetails: crmConf.tokenDetails,
  }
  bitsFetch(getAssigmentRulesParams, 'zcrm_get_assignment_rules')
    .then(result => {
      if (result?.success) {
        const newConf = { ...crmConf }
        if (!newConf.default.assignmentRules) {
          newConf.default.assignmentRules = {}
        }
        if (result.data.tokenDetails) {
          newConf.tokenDetails = result.data.tokenDetails
        }
        newConf.default.assignmentRules[module] = { ...result.data.assignmentRules }
        setCrmConf({ ...newConf })
        setSnackbar({ show: true, msg: __('Assignment Rules refreshed', 'bitwelzp') })
      } else {
        setSnackbar({ show: true, msg: __('Assignment Rules refresh failed. please try again', 'bitwelzp') })
      }
      setisLoading(false)
    })
    .catch(() => setisLoading(false))
}

export const generateMappedField = (recordTab, crmConf, uploadFields) => {
  const module = recordTab === 0 ? crmConf.module : crmConf.relatedlists[recordTab - 1].module
  const layout = recordTab === 0 ? crmConf.layout : crmConf.relatedlists[recordTab - 1].layout

  if (uploadFields) {
    return crmConf.default.layouts[module][layout].requiredFileUploadFields.length > 0 ? crmConf.default.layouts[module][layout].requiredFileUploadFields.map(field => ({ formField: '', zohoFormField: field })) : [{ formField: '', zohoFormField: '' }]
  }
  return crmConf.default.layouts[module][layout].required.length > 0 ? crmConf.default.layouts[module][layout].required.map(field => ({ formField: '', zohoFormField: field })) : [{ formField: '', zohoFormField: '' }]
}

export const checkMappedFields = (crmConf) => {
  const mappedFields = crmConf?.field_map ? crmConf.field_map.filter(mappedField => (!mappedField.formField && mappedField.zohoFormField && crmConf?.default?.layouts?.[crmConf.module]?.[crmConf.layout]?.required.indexOf(mappedField.zohoFormField) !== -1)) : []
  const mappedUploadFields = crmConf?.upload_field_map ? crmConf.upload_field_map.filter(mappedField => (!mappedField.formField && mappedField.zohoFormField && crmConf.default.layouts[crmConf.module][crmConf.layout].requiredFileUploadFields.indexOf(mappedField.zohoFormField) !== -1)) : []
  const mappedRelatedFields = crmConf.relatedlists.map(relatedlist => relatedlist.field_map.filter(mappedField => !mappedField.formField && mappedField.zohoFormField))
  const mappedRelatedUploadFields = crmConf.relatedlists.map(relatedlist => relatedlist.upload_field_map.filter(mappedField => !mappedField.formField && mappedField.zohoFormField))

  if (mappedFields.length > 0 || mappedUploadFields.length > 0 || mappedRelatedFields.some(relatedField => relatedField.length) || mappedRelatedUploadFields.some(relatedField => relatedField.length)) {
    return false
  }

  return true
}
export const handleAuthorize = (confTmp, setConf, setError, setisAuthorized, setisLoading, setSnackbar) => {
  if (!confTmp.dataCenter || !confTmp.clientId || !confTmp.clientSecret) {
    setError({
      dataCenter: !confTmp.dataCenter ? __('Data center cann\'t be empty', 'bitwelzp') : '',
      clientId: !confTmp.clientId ? __('Client ID cann\'t be empty', 'bitwelzp') : '',
      clientSecret: !confTmp.clientSecret ? __('Secret key cann\'t be empty', 'bitwelzp') : '',
    })
    return
  }

  setisLoading(true)
  const scopes = 'ZohoCRM.modules.ALL,ZohoCRM.settings.ALL,ZohoCRM.users.Read,zohocrm.files.CREATE'
  const apiEndpoint = `https://accounts.zoho.${confTmp.dataCenter}/oauth/v2/auth?scope=${scopes}&response_type=code&client_id=${confTmp.clientId}&prompt=Consent&access_type=offline&redirect_uri=${encodeURIComponent(window.location.href)}/redirect`
  const authWindow = window.open(apiEndpoint, 'zohoCRM', 'width=400,height=609,toolbar=off')
  const popupURLCheckTimer = setInterval(() => {
    if (authWindow.closed) {
      clearInterval(popupURLCheckTimer)
      let grantTokenResponse = {}
      let isauthRedirectLocation = false
      const bitformsZoho = localStorage.getItem('__bitforms_zohoCRM')
      if (bitformsZoho) {
        isauthRedirectLocation = true
        grantTokenResponse = JSON.parse(bitformsZoho)
        localStorage.removeItem('__bitforms_zohoCRM')
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
  tokenRequestParams.dataCenter = confTmp.dataCenter
  tokenRequestParams.clientId = confTmp.clientId
  tokenRequestParams.clientSecret = confTmp.clientSecret
  tokenRequestParams.redirectURI = `${encodeURIComponent(window.location.href)}/redirect`
  bitsFetch(tokenRequestParams, 'zcrm_generate_token')
    .then(result => result)
    .then(result => {
      if (result && result.success) {
        const newConf = { ...confTmp }
        console.log(newConf)
        newConf.tokenDetails = result.data
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
