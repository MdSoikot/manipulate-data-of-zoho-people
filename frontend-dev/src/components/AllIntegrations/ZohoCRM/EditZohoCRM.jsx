/* eslint-disable no-param-reassign */

import { useState } from 'react'
import { useHistory, useParams } from 'react-router-dom'
import { __ } from '../../../Utils/i18nwrap'
import SnackMsg from '../../Utilities/SnackMsg'
import { saveIntegConfig } from '../IntegrationHelpers/IntegrationHelpers'
import IntegrationStepThree from '../IntegrationHelpers/IntegrationStepThree'
import { checkMappedFields, handleInput } from './ZohoCRMCommonFunc'
import ZohoCRMIntegLayout from './ZohoCRMIntegLayout'

function EditZohoCRM({ formFields, setIntegration, integrations, allIntegURL }) {
  const history = useHistory()
  const { id, formID } = useParams()

  const [crmConf, setCrmConf] = useState({ ...integrations[id] })
  // const [crmConf, setCrmConf] = useState({ ...(typeof integrations[id].integration_details === 'string' ? JSON.parse(integrations[id].integration_details) : integrations[id].integration_details) })
  const [isLoading, setisLoading] = useState(false)
  const [snack, setSnackbar] = useState({ show: false })
  const [tab, settab] = useState(0)

  console.log('crmConf', crmConf)

  const saveConfig = () => {
    if (!checkMappedFields(crmConf)) {
      setSnackbar({ show: true, msg: __('Please map mandatory fields', 'bitwelzp') })
      return
    }
    const resp = saveIntegConfig(formID, integrations, setIntegration, allIntegURL, crmConf, history, id, 1)
    resp.then(res => {
      console.clear()
      if (res.success) {
        setSnackbar({ show: true, msg: res?.data })
        console.log('res', res)
        setTimeout(() => {
          history.push(allIntegURL)
        }, 200);
      } else {
        setSnackbar({ show: true, msg: res?.data || res })
      }
    })
  }

  return (
    <div style={{ width: 900 }}>
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />

      <div className="flx mt-3">
        <b className="wdt-100 d-in-b">{__('Integration Name:', 'bitwelzp')}</b>
        <input className="btcd-paper-inp w-7" onChange={e => handleInput(e, tab, crmConf, setCrmConf)} name="name" value={crmConf.name} type="text" placeholder={__('Integration Name...', 'bitwelzp')} />
      </div>

      <ZohoCRMIntegLayout
        tab={tab}
        settab={settab}
        formID={formID}
        formFields={formFields}
        handleInput={(e) => handleInput(e, tab, crmConf, setCrmConf, formID, setisLoading, setSnackbar)}
        crmConf={crmConf}
        setCrmConf={setCrmConf}
        isLoading={isLoading}
        setisLoading={setisLoading}
        setSnackbar={setSnackbar}
      />

      <IntegrationStepThree
        edit
        saveConfig={saveConfig}
        disabled={crmConf.module === '' || crmConf.layout === '' || crmConf.field_map.length < 1}
      />
      <br />
    </div>
  )
}

export default EditZohoCRM
