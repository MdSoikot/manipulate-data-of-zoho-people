/* eslint-disable no-undef */
/* eslint-disable react/jsx-props-no-spreading */
/* eslint-disable no-console */
/* eslint-disable react/jsx-one-expression-per-line */

import { lazy, Suspense } from 'react'
import { HashRouter as Router, Routes, Route, NavLink, Link } from 'react-router-dom'
// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from './Utils/i18nwrap'
import './resource/icons/style.css'
import Loader from './components/Loaders/Loader'
import logo from './resource/img/integ/crm.svg'
import TableLoader from './components/Loaders/TableLoader'
import Settings from './pages/Settings'
import Authorization from './pages/Authorization'

const AllEmployees = lazy(() => import('./pages/AllEmployees'))
const FormDetails = lazy(() => import('./pages/FormDetails'))
const Error404 = lazy(() => import('./pages/Error404'))

function App() {
  const loaderStyle = { height: '90vh' }
  const navClass = ({ isActive }) => (isActive ? 'app-link-active' : undefined)

  return (
    <Suspense fallback={(<Loader className="g-c" style={loaderStyle} />)}>
      <Router future={{ v7_startTransition: true, v7_relativeSplatPath: true }}>
        <div className="Btcd-App">
          <div className="nav-wrp">
            <div className="flx">
              <div className="logo flx" title={__('Zoho People', 'bitwelzp')}>
                <Link to="/" className="flx">
                  <img src={logo} alt="logo" className="ml-2" />
                  <span className="ml-2">Zoho People</span>
                </Link>
              </div>
              <nav className="top-nav ml-2">
                <NavLink end to="/" className={navClass}>
                  {__('All Employees', 'bitwelzp')}
                </NavLink>
                <NavLink end to="/formdetails" className={navClass}>
                  {__('All Reviews', 'bitwelzp')}
                </NavLink>
                <NavLink end to="/authorization" className={navClass}>
                  {__('Authorization', 'bitwelzp')}
                </NavLink>
                <NavLink end to="/settings" className={navClass}>
                  {__('Settings', 'bitwelzp')}
                </NavLink>
              </nav>
            </div>
          </div>

          <div className="route-wrp">
            <Routes>
              <Route
                path="/"
                element={(
                  <Suspense fallback={<TableLoader />}>
                    <AllEmployees />
                  </Suspense>
                )}
              />
              <Route
                path="/formdetails"
                element={(
                  <Suspense fallback={<TableLoader />}>
                    <FormDetails />
                  </Suspense>
                )}
              />
              <Route
                path="/authorization/*"
                element={(
                  <Suspense fallback={<Loader className="g-c" style={loaderStyle} />}>
                    <Authorization />
                  </Suspense>
                )}
              />
              <Route
                path="/settings/*"
                element={(
                  <Suspense fallback={<Loader className="g-c" style={loaderStyle} />}>
                    <Settings />
                  </Suspense>
                )}
              />
              <Route path="*" element={<Error404 />} />
            </Routes>
          </div>
        </div>
      </Router>
    </Suspense>
  )
}

export default App
