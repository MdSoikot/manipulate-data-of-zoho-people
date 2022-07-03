/* eslint-disable no-param-reassign */
import MultiSelect from 'react-multiple-select-dropdown-lite'

import { __ } from '../../Utils/i18nwrap'
import 'react-multiple-select-dropdown-lite/dist/index.css'
import Button from './Button'
import MtInput from './MtInput'
import MtSelect from './MtSelect'

function ActionBlock({ formFields, fields, action, lgcGrpInd, actionInd, setworkFlows, actionType }) {
  let fieldKey = ''
  let type = ''

  if (formFields !== null) {
    formFields.map(itm => {
      if (itm.key === action.field) {
        type = itm.type
        fieldKey = itm.key
      }
    })
  }

  const changeAction = val => {
    setworkFlows(prv => {
      prv[lgcGrpInd].actions[actionInd].action = val
      return [...prv]
    })
  }

  const changeAtnVal = val => {
    setworkFlows(prv => {
      prv[lgcGrpInd].actions[actionInd].val = val
      return [...prv]
    })
  }

  const changeAtnField = val => {
    setworkFlows(prv => {
      prv[lgcGrpInd].actions[actionInd].field = val
      prv[lgcGrpInd].actions[actionInd].val = ''
      return [...prv]
    })
  }

  const delAction = () => {
    setworkFlows(prv => {
      if (prv[lgcGrpInd].actions.length > 1) {
        prv[lgcGrpInd].actions.splice(actionInd, 1)
      }
      return [...prv]
    })
  }

  return (
    <div className="flx pos-rel btcd-logic-blk">
      <MtSelect
        label="Form Fields"
        value={action.field}
        onChange={e => changeAtnField(e.target.value)}
        style={{ width: 720 }}
      >
        <option value="">{__('Select One', 'bitwelzp')}</option>
        {formFields.map(itm => itm.type !== 'file' && <option key={`ff-Ab-${itm.key}`} value={itm.key}>{itm.name}</option>)}
      </MtSelect>

      <svg height="35" width="100" className="mt-1">
        <line x1="0" y1="20" x2="50" y2="20" style={{ stroke: '#b9c5ff', strokeWidth: 1 }} />
      </svg>

      <MtSelect
        label="Action"
        onChange={e => changeAction(e.target.value)}
        value={action.action}
        style={{ width: 400 }}
        className="w-4"
      >
        <option value="">{__('Select One', 'bitwelzp')}</option>
        <option value="value">{__('Value', 'bitwelzp')}</option>
        {actionType !== 'onsubmit' && <option value="disable">{__('Disable', 'bitwelzp')}</option>}
        {actionType !== 'onsubmit' && <option value="readonly">{__('Readonly', 'bitwelzp')}</option>}
        {actionType !== 'onsubmit' && <option value="enable">{__('Enable', 'bitwelzp')}</option>}
        {actionType !== 'onsubmit' && <option value="hide">{__('Hide', 'bitwelzp')}</option>}
        {actionType !== 'onsubmit' && <option value="show">{__('Show', 'bitwelzp')}</option>}
      </MtSelect>

      {action.action === 'value' && (
        <>
          <svg height="35" width="100" className="mt-1">
            <line x1="0" y1="20" x2="40" y2="20" style={{ stroke: '#b9c5ff', strokeWidth: 1 }} />
          </svg>

          {type === 'select' || type === 'check' || type === 'radio'
            ? (
              <MultiSelect
                className="msl-wrp-options btcd-paper-drpdwn w-10"
                defaultValue={action.val}
                onChange={changeAtnVal}
                options={type === 'select' ? fields?.[fieldKey]?.opt : (type === 'check' || type === 'radio') && fields?.[fieldKey]?.opt?.map(opt => ({ label: opt.lbl, value: opt.lbl }))}
                customValue={fields?.[fieldKey]?.customOpt}
                // eslint-disable-next-line no-nested-ternary
                singleSelect={type === 'select' ? !fields?.[fieldKey]?.mul : type === 'check' ? false : type === 'radio' && true}
              />
            )
            : (
              <MtInput onChange={e => changeAtnVal(e.target.value)} label="Value" value={action.val} />
            )}
        </>
      )}

      <div className="btcd-li-side-btn mt-2">
        <Button onClick={delAction} icn className="ml-2 sh-sm white">
          <span className="btcd-icn icn-trash-2" />
        </Button>
      </div>
    </div>
  )
}

export default ActionBlock
