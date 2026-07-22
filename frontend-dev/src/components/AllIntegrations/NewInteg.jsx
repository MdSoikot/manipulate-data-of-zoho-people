import { lazy, Suspense } from 'react'
import { Link, Route, useParams, useRouteMatch } from 'react-router-dom'
import { __ } from '../../Utils/i18nwrap'
import Loader from '../Loaders/Loader'

const ZohoCRM = lazy(() => import('./ZohoCRM/ZohoCRM'))
export default function NewInteg({ allIntegURL, formFields, setIntegration, integrations }) {
  const { integUrlName } = useParams()

  const NewIntegs = () => {
    switch (integUrlName) {
      case 'Zoho CRM':
        return <ZohoCRM allIntegURL={allIntegURL} formFields={formFields} integrations={integrations} setIntegration={setIntegration} />
      default:
        break
    }
    return <></>
  }

  return (
    <div>
      <div className="flx">
        <Link to={allIntegURL} className="btn btcd-btn-o-gray">
          <span className="btcd-icn icn-chevron-left" />
          &nbsp;Back
        </Link>
        <div className="w-8 txt-center">
          <div className="mb-1"><b className="f-lg">{integUrlName}</b></div>
          <div>{__('Integration Settings', 'bitwelzp')}</div>
        </div>
      </div>

      <Suspense fallback={<Loader className="g-c" style={{ height: '90vh' }} />}>
        <NewIntegs />
      </Suspense>
    </div>
  )
}
