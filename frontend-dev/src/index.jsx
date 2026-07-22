import { lazy, Suspense } from 'react'
import ReactDOM from 'react-dom'
import { AllFormContextProvider } from './Utils/AllFormContext'
import Loader from './components/Loaders/Loader'

const App = lazy(() => import('./App'))

if (typeof bitwelzp !== 'undefined' && bitwelzp.baseURL && `${window.location.pathname + window.location.search}#` !== bitwelzp.baseURL) {
  bitwelzp.baseURL = `${window.location.pathname + window.location.search}#`
}
if (window.location.hash === '') {
  window.location = `${window.location.href}#/`
}
ReactDOM.render(
  <AllFormContextProvider>
    <Suspense fallback={(
      <Loader style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        height: '90vh',
      }}
      />
    )}
    >
      <App />
    </Suspense>
  </AllFormContextProvider>, document.getElementById('btcd-app'),
)
