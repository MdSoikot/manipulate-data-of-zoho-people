/* eslint-disable-next-line no-undef */
import { useState, useEffect } from 'react'
import { Link, Route, Switch, useHistory, useParams, useRouteMatch } from 'react-router-dom'
import { __ } from '../Utils/i18nwrap'
import zohoCRM from '../resource/img/integ/crm.svg'
import bitsFetch from '../Utils/bitsFetch'
import EditInteg from './AllIntegrations/EditInteg'
import IntegInfo from './AllIntegrations/IntegInfo'
import Log from './AllIntegrations/Log'
import NewInteg from './AllIntegrations/NewInteg'
import ConfirmModal from './Utilities/ConfirmModal'
import SnackMsg from './Utilities/SnackMsg'
import Modal from './Utilities/Modal'
import EditIcn from '../Icons/EditIcn'
import TimeIcn from '../Icons/TimeIcn'
import SingleToggle2 from './Utilities/SingleToggle2'


function Integrations() {
  const [integrations, setIntegration] = useState([])
  const [formFields, setformFields] = useState(null)
  const [showMdl, setShowMdl] = useState(false)
  const [confMdl, setconfMdl] = useState({ show: false })
  const [snack, setSnackbar] = useState({ show: false })
  const { path, url } = useRouteMatch()
  const allIntegURL = url
  const history = useHistory()
  const { formID } = useParams()
  const integs = [
    { type: 'Zoho CRM', logo: zohoCRM },
  ]

  const [availableIntegs, setAvailableIntegs] = useState(integs)


  useEffect(() => {
    const data = { formId: formID }
    bitsFetch(data, 'ffp/get/form')
      .then(res => {
        if ('success' in res && res.success) {
          console.log(res)
          if (res.data?.fields) {
            setformFields(res.data.fields)
          }
          if (res.data?.integrations) {
            setIntegration(res.data.integrations)
          }
          
        }
      }).catch(() => {
        setSnackbar({ ...{ show: true, msg: __('Failed to load form data', 'bitwelzp') } })
      })
  }, [])

  const handleStatus = (ev, id) => {
    const tempIntegration = [ ...integrations ]
    const toggleStatus = tempIntegration[id].status == 1 ? 0 : 1
    const data = {formID, id: tempIntegration[id].id, status: toggleStatus }
    bitsFetch(data, 'integration/toggleStatus')
      .then(res => {
        if (res && res.success) {
          tempIntegration[id].status = toggleStatus
          setIntegration(tempIntegration)
        }
        if ('data' in res && res.data) {
          setSnackbar({ ...{ show: true, msg: res.data } })
        }
      }).catch(() => {
        setSnackbar({ ...{ show: true, msg: __('Failed to toogel integration status', 'bitwelzp') } })
      })
    console.log(tempIntegration[id].status, ev, id, data)
  }

  const removeInteg = i => {
    const tempIntegration = { ...integrations[i] }
    const newInteg = [...integrations]
    newInteg.splice(i, 1)
    setIntegration(newInteg)
    bitsFetch({ formId: formID, id: tempIntegration.id }, 'integration/delete')
      .then(response => {
        if (response && response.success) {
          setSnackbar({ show: true, msg: `${response.data}` })
        } else if (response && !response.success) {
          newInteg.splice(i, 0, tempIntegration)
          setIntegration([...newInteg])
          setSnackbar({ show: true, msg: `${__('Integration deletion failed Cause', 'bitwelzp')}:${response.data}. ${__('please try again', 'bitwelzp')}` })
        } else {
          newInteg.splice(i, 0, tempIntegration)
          setIntegration([...newInteg])
          setSnackbar({ show: true, msg: __('Integration deletion failed. please try again', 'bitwelzp') })
        }
      })
  }

  const inteDelConf = i => {
    confMdl.btnTxt = __('Delete', 'bitwelzp')
    confMdl.body = __('Are you sure to delete this integration?', 'bitwelzp')
    confMdl.btnClass = ''
    confMdl.action = () => { removeInteg(i); closeConfMdl() }
    confMdl.show = true
    setconfMdl({ ...confMdl })
  }

  const getLogo = type => {
    return <img alt={'zohoCRM'} loading="lazy" src={zohoCRM} />
  }

  const setNewInteg = (type) => {
    closeIntegModal()
    history.push(`${allIntegURL}/new/${type}`)
  }

  const closeIntegModal = () => {
    setShowMdl(false)
    setTimeout(() => setAvailableIntegs(integs), 500)
  }

  const closeConfMdl = () => {
    confMdl.show = false
    setconfMdl({ ...confMdl })
  }

  return (
    <div className="btcd-s-wrp">
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <ConfirmModal
        show={confMdl.show}
        close={closeConfMdl}
        btnTxt={confMdl.btnTxt}
        btnClass={confMdl.btnClass}
        body={confMdl.body}
        action={confMdl.action}
      />
      <Switch>
        <Route exact path={path}>
          <h2>{__('Integrations', 'bitwelzp')}</h2>
          <div className="flx flx-wrp">
           <div role="button" className="btcd-inte-card flx flx-center add-inte mr-4 mt-3" tabIndex="0" onClick={() => setNewInteg('Zoho CRM')} onKeyPress={() => setNewInteg('Zoho CRM')}>
              <div>+</div>
            </div>

            {integrations.map((inte, i) => (
              <div role="button" className="btcd-inte-card mr-4 mt-3" key={`inte-${i + 3}`}>
                {console.log('inte.status', inte.status, inte.status == 1)}
                <SingleToggle2 className="flx mt-2 pos-abs r-n-1 z-9" action={(e) => handleStatus(e, i)} checked={inte.status == 1 ? true : false} />
                {getLogo('Zoho CRM')}
                <div className="btcd-inte-atn txt-center">
                  <Link to={`${allIntegURL}/edit/${i}`} className="btn btcd-btn-o-blue btcd-btn-sm mr-2 tooltip pos-rel" style={{ '--tooltip-txt': `'${__('Edit', 'bitwelzp')}'` }} type="button">
                    <EditIcn size="15   " />
                  </Link>
                  <button className="btn btcd-btn-o-blue btcd-btn-sm mr-2 tooltip pos-rel" style={{ '--tooltip-txt': `'${__('Delete', 'bitwelzp')}'` }} onClick={() => inteDelConf(i)} type="button">
                    <span className="btcd-icn icn-trash-2" />
                  </button>
                  {typeof (integs.find(int => int.type === inte.type)?.info) !== 'boolean' && (
                    <Link to={`${allIntegURL}/info/${i}`} className="btn btcd-btn-o-blue btcd-btn-sm mr-2 tooltip pos-rel" style={{ '--tooltip-txt': `'${__('Info', 'bitwelzp')}'` }} type="button">
                      <span className="btcd-icn icn-information-outline" />
                    </Link>
                  )}
                    <Link to={`${allIntegURL}/log/${i}`} className="btn btcd-btn-o-blue btcd-btn-sm tooltip pos-rel" style={{ '--tooltip-txt': `'${__('Log', 'bitwelzp')}'` }} type="button">
                      <TimeIcn size="15" />
                    </Link>
                </div>
                <div className="txt-center body w-10 py-1" title={`${inte.name} | ${inte.type}`}>
                  <div>{inte.name}</div>
                  <small className="txt-dp">{inte.type}</small>
                </div>
              </div>
            ))}
          </div>
        </Route>

        <Route path={`${path}/new/:integUrlName`}>
          <NewInteg allIntegURL={allIntegURL} formFields={formFields} integrations={integrations} setIntegration={setIntegration} />
        </Route>

        {integrations?.length
          && (
            <Route exact path={`${path}/edit/:id`}>
              <EditInteg allIntegURL={allIntegURL} formFields={formFields} integrations={integrations} setIntegration={setIntegration} />
            </Route>
          )}
        {integrations && integrations.length > 0
          && (
            <>
            <Route exact path={`${path}/info/:id`}>
              <IntegInfo allIntegURL={allIntegURL} integrations={integrations} />
            </Route>
            <Route exact path={`${path}/log/:id`}>
              <Log allIntegURL={allIntegURL} integrations={integrations} />
            </Route>
            </>
          )}
      </Switch>

    </div>
  )
}

export default Integrations
