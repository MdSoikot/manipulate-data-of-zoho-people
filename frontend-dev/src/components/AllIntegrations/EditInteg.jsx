import { lazy, Suspense } from 'react'
import { Link, useParams } from 'react-router-dom'
import { __ } from '../../Utils/i18nwrap'
import Loader from '../Loaders/Loader'

const EditZohoCRM = lazy(() => import('./ZohoCRM/EditZohoCRM'))

export default function EditInteg({ allIntegURL, formFields, setIntegration, integrations }) {
  const { id } = useParams()
  const IntegType = () => {
    return <EditZohoCRM allIntegURL={allIntegURL} formFields={formFields} integrations={integrations} setIntegration={setIntegration} />
  }

  return (
    <div>
      <div className="flx">
        <Link to={allIntegURL} className="btn btcd-btn-o-gray">
          <span className="btcd-icn icn-chevron-left" />
          &nbsp;Back
        </Link>
        <div className="w-8 txt-center">
          <b className="f-lg">{integrations[id].type}</b>
          <div>{__('Integration Settings', 'bitwelzp')}</div>
        </div>
      </div>
      <Suspense fallback={<Loader className="g-c" style={{ height: '90vh' }} />}>
        <IntegType />
      </Suspense>
    </div>
  )
}
