import { lazy, Suspense } from 'react'
import { createRoot } from 'react-dom/client'
import { AllFormContextProvider } from './Utils/AllFormContext'
import Loader from './components/Loaders/Loader'

const App = lazy(() => import('./App'))

createRoot(document.getElementById('btcd-app')).render(
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
  </AllFormContextProvider>,
)
