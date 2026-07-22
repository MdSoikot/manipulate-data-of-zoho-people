/* eslint-disable no-undef */
/* eslint-disable react/jsx-props-no-spreading */
/* eslint-disable no-console */
/* eslint-disable react/jsx-one-expression-per-line */

import { lazy, Suspense } from 'react'
import { BrowserRouter as Router, Switch, Route, NavLink, Link } from 'react-router-dom'
import './resource/sass/app.scss'
// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from './Utils/i18nwrap'
import './resource/icons/style.css'
import Loader from './components/Loaders/Loader'
import logo from './resource/img/integ/crm.svg'
import TableLoader from './components/Loaders/TableLoader'
import Settings from './pages/Settings'
import Authorization from './pages/Authorization'
import { RecoilRoot } from 'recoil';

const AllEmployees = lazy(() => import('./pages/AllEmployees'))
const FormDetails = lazy(() => import('./pages/FormDetails'))
const Error404 = lazy(() => import('./pages/Error404'))

function App() {
  const loaderStyle = { height: '90vh' }

  return (
    <RecoilRoot>
      <Suspense fallback={(<Loader className="g-c" style={loaderStyle} />)}>
        <Router basename={typeof bitwelzp !== 'undefined' ? bitwelzp.baseURL : '/'}>
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
                  <NavLink
                    exact
                    to="/"
                    activeClassName="app-link-active"
                  >
                    {__('All Employees', 'bitwelzp')}
                  </NavLink>
                  <NavLink
                    exact
                    to="/formdetails"
                    activeClassName="app-link-active"
                  >
                    {__('All Reviews', 'bitwelzp')}
                  </NavLink>
                  <NavLink
                    exact
                    to="/authorization"
                    activeClassName="app-link-active"
                  >
                    {__('Authorization', 'bitwelzp')}
                  </NavLink>
                  <NavLink
                    exact
                    to="/settings"
                    activeClassName="app-link-active"
                  >
                    {__('Settings', 'bitwelzp')}
                  </NavLink>
                </nav>
              </div>
            </div>

            <div className="route-wrp">
              <Switch>
                <Route exact path="/">
                  <Suspense fallback={<TableLoader />}>
                    <AllEmployees />
                  </Suspense>
                </Route>
                <Route exact path="/formdetails">
                  <Suspense fallback={<TableLoader />}>
                    <FormDetails />
                  </Suspense>
                </Route>
                <Route path="/authorization">
                  <Suspense fallback={<Loader className="g-c" style={loaderStyle} />}>
                    <Authorization />
                  </Suspense>
                </Route>
                <Route path="/settings">
                  <Suspense fallback={<Loader className="g-c" style={loaderStyle} />}>
                    <Settings />
                  </Suspense>
                </Route>
                <Route path="*">
                  <Error404 />
                </Route>
              </Switch>
            </div>
          </div>
        </Router>
      </Suspense>
    </RecoilRoot>

  )
}

export default App
