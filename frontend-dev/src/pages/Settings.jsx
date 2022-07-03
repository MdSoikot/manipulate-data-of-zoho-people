import { memo, useState } from 'react'
import SingleToggle2 from '../components/Utilities/SingleToggle2'
import SnackMsg from '../components/Utilities/SnackMsg'
import bitsFetch from '../Utils/bitsFetch'
import { __ } from '../Utils/i18nwrap'

function Settings() {
  const [erase, setErase] = useState(bitwelzp && bitwelzp.erase_all)
  const [snack, setSnackbar] = useState({ show: false })

  const toggleEraseAll = (e) => {
    const toggle = e.target.checked
    const data = { toggle }
    bitsFetch(data, 'erase_all')
      .then(res => {
        if ('success' in res && res.success) {
          setErase(!erase)
        }
        setSnackbar({ ...{ show: true, msg: res.data } })
      }).catch(() => {
        setSnackbar({ ...{ show: true, msg: __('Failed to toggle', 'bitwelzp') } })
      })
  }
  console.log('erase',erase)

  return (
    <div className="btcd-f-settings">
      <SnackMsg snack={snack} setSnackbar={setSnackbar} />
      <div id="btcd-settings-wrp" className="btcd-s-wrp">
        <div className="w-6 mt-3">
          <div className="flx flx-between sh-sm br-10 btcd-setting-opt">
            <div>
              <b>
                <span className="btcd-icn  icn-trash-fill mr-2" />
                {__('Erase all data of this plugin in deletion', 'bitwelzp')}
              </b>
            </div>
            <SingleToggle2 action={toggleEraseAll} checked={erase} className="flx" />
          </div>
        </div>
        <div className="mb-50" />
      </div>
    </div>
  )
}

export default memo(Settings)
