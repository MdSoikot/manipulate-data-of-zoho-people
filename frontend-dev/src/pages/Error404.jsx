import { useEffect, useState } from 'react'
import { Link, useHistory } from 'react-router-dom'
import { __ } from '../Utils/i18nwrap'
import space from '../resource/img/space.svg'

export default function Error404() {
  const [sec, setsec] = useState(9)
  const history = useHistory()
  useEffect(() => {
    setTimeout(() => {
      if (sec === 0) {
        history.push('/')
      }
      setsec(sec - 1)
    }, 1000)
  }, [history, sec])

  return (
    <div className="error-404">
      <div>
        <div className="four">{__('404', 'bitwelzp')}</div>
        <div className="t">{__('Lost In Space', 'bitwelzp')}</div>
        <br />
        {__('Redirecting Home in', 'bitwelzp')}
        {' '}
        {sec}
        <br />
        <br />
        <Link to="/" className="btn dp-blue btcd-btn-lg">{__('Go Home', 'bitwelzp')}</Link>
      </div>
      <img src={space} alt="404 not found" />
    </div>
  )
}
