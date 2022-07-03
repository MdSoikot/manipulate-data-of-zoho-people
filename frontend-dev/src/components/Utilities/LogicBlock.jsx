import MultiSelect from 'react-multiple-select-dropdown-lite'
import { __ } from '../../Utils/i18nwrap'
import MtSelect from './MtSelect'
import MtInput from './MtInput'
import Button from './Button'
import 'react-multiple-select-dropdown-lite/dist/index.css'
import CloseIcn from '../../Icons/CloseIcn'

function LogicBlock({ fieldVal, formFields, fields, delLogic, lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd, value, addInlineLogic, changeLogic, logicValue, changeValue, changeFormField }) {
  let type = ''
  let fldType = ''
  let fieldKey = ''
  if (formFields !== null) {
    formFields.map(itm => {
      if (itm.key === fieldVal) {
        if (itm.type.match(/^(check|radio)$/)) {
          type = 'text'
        } else {
          type = itm.type
        }
        fldType = itm.type
        fieldKey = itm.key
      }
    })
  }

  const getOptions = () => {
    let options = []

    if (fldType === 'select') {
      options = fields?.[fieldKey]?.opt
    } else {
      options = fields?.[fieldKey]?.opt?.map(opt => ({ label: opt.lbl, value: opt.lbl }))
    }

    return options
  }

  return (
    <div className="flx pos-rel btcd-logic-blk">
      <MtSelect
        label="Form Fields"
        value={fieldVal !== undefined && fieldVal}
        style={{ width: 720 }}
        onChange={e => changeFormField(e.target.value, lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)}
      >
        <option value="">{__('Select Form Field', 'bitwelzp')}</option>
        {formFields.map(itm => !itm.type.match(/^(file-up|recaptcha)$/) && <option key={`ff-lb-${itm.key}`} value={itm.key}>{itm.name}</option>)}
      </MtSelect>

      <svg height="35" width="100" className="mt-1">
        <line x1="0" y1="20" x2="40" y2="20" style={{ stroke: '#b9c5ff', strokeWidth: 1 }} />
      </svg>

      <MtSelect
        label="Logic"
        value={logicValue}
        style={{ width: 400 }}
        onChange={e => changeLogic(e.target.value, lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)}
        className="w-4"
      >
        <option value="">{__('Select One', 'bitwelzp')}</option>
        <option value="equal">{__('Equal', 'bitwelzp')}</option>
        <option value="not_equal">{__('Not Equal', 'bitwelzp')}</option>
        <option value="null">{__('Is Null', 'bitwelzp')}</option>
        <option value="not_null">{__('Is Not Null', 'bitwelzp')}</option>
        {!type.match(/^(date|time|datetime|month|week)$/) && <option value="contain">{__('Contain', 'bitwelzp')}</option>}
        {((fldType === 'select' && fields?.[fieldKey]?.mul) || fldType === 'check') && <option value="contain_all">{__('Contain All', 'bitwelzp')}</option>}
        {!type.match(/^(date|time|datetime|month|week)$/) && <option value="not_contain">{__('Not Contain', 'bitwelzp')}</option>}
        {type === 'number' && <option value="greater">{__('Greater Than', 'bitwelzp')}</option>}
        {type === 'number' && <option value="less">{__('Less Than', 'bitwelzp')}</option>}
        {type === 'number' && <option value="greater_or_equal">{__('Greater Than or Equal', 'bitwelzp')}</option>}
        {type === 'number' && <option value="less_or_equal">{__('Less Than or Equal', 'bitwelzp')}</option>}
        {!type.match(/^(color|url|password|email|date|time|datetime|month|week)$/) && <option value="start_with">{__('Start With', 'bitwelzp')}</option>}
        {!type.match(/^(color|url|password|email|date|time|datetime|month|week)$/) && <option value="end_with">{__('End With', 'bitwelzp')}</option>}
      </MtSelect>

      <svg height="35" width="100" className="mt-1">
        <line x1="0" y1="20" x2="40" y2="20" style={{ stroke: '#b9c5ff', strokeWidth: 1 }} />
      </svg>

      {
        fldType.match(/select|check|radio/g)
          ? (
            <>
              <MultiSelect
                className="msl-wrp-options btcd-paper-drpdwn w-10"
                defaultValue={value}
                onChange={e => changeValue(e, lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)}
                options={getOptions()}
                customValue
                fldType={fldType}
              />
            </>
          ) : (
            <MtInput
              label="Value"
              type={type}
              disabled={logicValue === 'null' || logicValue === 'not_null'}
              onChange={e => changeValue(e.target.value, lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)}
              value={value}
            />
          )
      }

      <div className="btcd-li-side-btn">
        <Button onClick={() => delLogic(lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)} icn className="ml-2 white mr-2 sh-sm">
          <span className="btcd-icn icn-trash-2" />
        </Button>
        <Button onClick={() => addInlineLogic('and', lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)} className="white mr-2 sh-sm">
          <CloseIcn size="14" className="icn-rotate-45 mr-1" />
          AND
        </Button>
        <Button onClick={() => addInlineLogic('or', lgcGrpInd, lgcInd, subLgcInd, subSubLgcInd)} className="white sh-sm">
          <CloseIcn size="14" className="icn-rotate-45 mr-1" />
          OR
        </Button>
      </div>
    </div>
  )
}

export default LogicBlock
