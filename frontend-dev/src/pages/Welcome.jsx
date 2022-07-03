// eslint-disable-next-line import/no-extraneous-dependencies
import { __ } from '../Utils/i18nwrap'
import greeting from '../resource/img/home.svg'
import bitsFetch from '../Utils/bitsFetch'
import { $integConfig } from '../Utils/GlobalStates'
import { useRecoilState } from 'recoil'
import { useState, } from 'react'


export default function Welcome() {
  const [integConfig, setIntegConfig] = useRecoilState($integConfig)
  const fetchData = () => {
    console.log(integConfig)
  }

  return (
    <div className="btcd-greeting">
      <img src={greeting} alt="" />
      <h2>{__('Welcome to Zoho Peopole integration', 'bitwelzp')}</h2>
      <div className="sub">
        {__('Thank you for installing.', 'bitwelzp')}
      </div>
      <div>
      </div>
      <button onClick={fetchData} type="button" className="btn round btcd-btn-lg dp-blue">{__('Fetch Data', 'bitwelzp')}</button>
    </div>
  )
}
