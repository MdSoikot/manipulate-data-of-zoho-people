import { lazy, Suspense, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { __ } from '../../Utils/i18nwrap'
import SnackMsg from '../Utilities/SnackMsg'

const ZohoCRMAuthorization = lazy(() => import('./ZohoCRM/ZohoCRMAuthorization'))
const Loader = lazy(() => import('../Loaders/Loader'))

export default function IntegInfo({ allIntegURL, integrations }) {
  const { id } = useParams()
  const [snack, setSnackbar] = useState({ show: false })
  const integ = integrations[id]

  // route is info/:id but for redirect uri need to make new/:type
  let location = window.location.toString()

  const toReplaceInd = location.indexOf('/info')
  location = window.encodeURI(`${location.slice(0, toReplaceInd)}/new/${integrations[id].type}`)

  const IntegInfoComponents = () => {
    return <ZohoCRMAuthorization crmConf={integ} step={1} redirectLocation={location} setSnackbar={setSnackbar} isInfo />
  }

  return (
    <>
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <div className="flx">
        <Link to={allIntegURL} className="btn btcd-btn-o-gray">
          <span className="btcd-icn icn-chevron-left" />
          &nbsp;Back
        </Link>
        <div className="w-8 txt-center">
          <b className="f-lg">{integrations[id].type}</b>
          <div>{__('Integration Info', 'bitwelzp')}</div>
        </div>
      </div>

      <Suspense fallback={<Loader className="g-c" style={{ height: '90vh' }} />}>
        <IntegInfoComponents />
      </Suspense>
    </>
  )
}
