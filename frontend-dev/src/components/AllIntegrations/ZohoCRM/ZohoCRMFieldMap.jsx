import { __ } from '../../../Utils/i18nwrap'
import MtInput from '../../Utilities/MtInput'
import { addFieldMap, delFieldMap, handleCustomValue, handleFieldMapping } from '../IntegrationHelpers/IntegrationHelpers'

export default function ZohoCRMFieldMap({ i, formFields, uploadFields, field, crmConf, setCrmConf, tab }) {
  const module = tab === 0 ? crmConf.module : crmConf.relatedlists?.[tab - 1]?.module
  const layout = tab === 0 ? crmConf.layout : crmConf.relatedlists?.[tab - 1]?.layout

  let isNotRequired

  if (uploadFields) {
    isNotRequired = field.zohoFormField === '' || crmConf.default.layouts?.[module]?.[layout]?.requiredFileUploadFields?.indexOf(field.zohoFormField) === -1
  } else {
    isNotRequired = field.zohoFormField === '' || crmConf.default.layouts?.[module]?.[layout]?.required?.indexOf(field.zohoFormField) === -1
  }

  return (
    <div
      className="flx mt-2 mr-1"
    >
      <div className="flx integ-fld-wrp">
        <select className="btcd-paper-inp mr-2" name="formField" value={field.formField || ''} onChange={(ev) => handleFieldMapping(ev, i, crmConf, setCrmConf, uploadFields, tab)}>
          <option value="">{__('Select Field', 'bitwelzp')}</option>
          {
            uploadFields ? formFields.map(f => f.type === 'file' && <option key={`ff-zhcrm-${f.name}`} value={f.name}>{f.label}</option>) : formFields.map(f => f.type !== 'file' && <option key={`ff-zhcrm-${f.name}`} value={f.name}>{f.label}</option>)
          }
          {!uploadFields && <option value="custom">{__('Custom...', 'bitwelzp')}</option>}
        </select>

        {field.formField === 'custom' && <MtInput onChange={e => handleCustomValue(e, i, crmConf, setCrmConf, tab)} label={__('Custom Value', 'bitwelzp')} className="mr-2" type="text" value={field.customValue} placeholder={__('Custom Value', 'bitwelzp')} />}

        <select className="btcd-paper-inp" disabled={!isNotRequired} name="zohoFormField" value={field.zohoFormField || ''} onChange={(ev) => handleFieldMapping(ev, i, crmConf, setCrmConf, uploadFields, tab)}>
          <option value="">{__('Select Field', 'bitwelzp')}</option>
          {
            uploadFields ? crmConf.default.layouts?.[module]?.[layout]?.fileUploadFields && Object.keys(crmConf.default.layouts[module][layout].fileUploadFields).filter(fld => fld.required !== true).map(fieldApiName => (
              isNotRequired
                ? !crmConf.default.layouts[module][layout].fileUploadFields[fieldApiName].required && (
                  <option key={fieldApiName} value={fieldApiName}>
                    {crmConf.default.layouts[module][layout].fileUploadFields[fieldApiName].display_label}
                  </option>
                )
                : (
                  <option key={fieldApiName} value={fieldApiName}>
                    {crmConf.default.layouts[module][layout].fileUploadFields[fieldApiName].display_label}
                  </option>
                )
            )) : crmConf.default.layouts?.[module]?.[layout]?.fields && Object.keys(crmConf.default.layouts[module][layout].fields).filter(fld => fld.required !== true).map(fieldApiName => (
              isNotRequired
                ? !crmConf.default.layouts[module][layout].fields[fieldApiName].required && (
                  <option key={fieldApiName} value={fieldApiName}>
                    {crmConf.default.layouts[module][layout].fields[fieldApiName].display_label}
                  </option>
                ) : (
                  <option key={fieldApiName} value={fieldApiName}>
                    {crmConf.default.layouts[module][layout].fields[fieldApiName].display_label}
                  </option>
                )
            ))
          }
        </select>
      </div>

      {
        isNotRequired && (
          <>
            <button
              onClick={() => addFieldMap(i, crmConf, setCrmConf, uploadFields, tab)}
              className="icn-btn sh-sm ml-2 mr-1"
              type="button"
            >
              +
            </button>
            <button onClick={() => delFieldMap(i, crmConf, setCrmConf, uploadFields, tab)} className="icn-btn sh-sm ml-1" type="button" aria-label="btn">
              <span className="btcd-icn icn-trash-2" />
            </button>
          </>
        )
      }
    </div>
  )
}
