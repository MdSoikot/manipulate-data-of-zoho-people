import { useEffect } from 'react'
import { __ } from '../../../Utils/i18nwrap'
import Loader from '../../Loaders/Loader'
import { addFieldMap } from '../IntegrationHelpers/IntegrationHelpers'
import ZohoCRMActions from './ZohoCRMActions'
import { handleTabChange, refreshLayouts, refreshRelatedList } from './ZohoCRMCommonFunc'
import ZohoCRMFieldMap from './ZohoCRMFieldMap'

export default function ZohoCRMRelatedRecord({ indx, tab, settab, formID, formFields, crmConf, setCrmConf, handleInput, isLoading, setisLoading, setSnackbar }) {
  useEffect(() => {
    handleTabChange(indx + 1, settab, formID, crmConf, setCrmConf, setisLoading, setSnackbar)
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [])

  // eslint-disable-next-line no-undef
  const isPro = typeof bitwelzp !== 'undefined' && bitwelzp.isPro
  return (
    <>
      {isLoading && (
        <Loader style={{
          display: 'flex',
          justifyContent: 'center',
          alignItems: 'center',
          height: 100,
          transform: 'scale(0.7)',
        }}
        />
      )}
      <br />
      <br />
      <div className="pos-rel">
        {!isPro && (
          <div className="pro-blur flx w-9">
            <div className="pro">
              {__('Available On', 'bitwelzp')}
              <a href="https://bitpress.pro/" target="_blank" rel="noreferrer">
                <span className="txt-pro">
                  {' '}
                  {__('Premium', 'bitwelzp')}
                </span>
              </a>
            </div>
          </div>
        )}
        <b className="wdt-100 d-in-b">{__('Related List:', 'bitwelzp')}</b>
        <select onChange={handleInput} name="module" value={crmConf?.relatedlists?.[tab - 1]?.module} className="btcd-paper-inp w-7" disabled={!crmConf.module}>
          <option value="">{__('Select Related Module', 'bitwelzp')}</option>
          {
            crmConf?.default?.relatedlists?.[crmConf.module] && Object.values(crmConf.default.relatedlists[crmConf.module]).map(relatedlistApiName => (
              <option key={relatedlistApiName.module} value={relatedlistApiName.module}>
                {relatedlistApiName.name}
              </option>
            ))
          }
        </select>
        <button onClick={() => refreshRelatedList(formID, crmConf, setCrmConf, setisLoading, setSnackbar)} className="icn-btn sh-sm ml-2 mr-2 tooltip" style={{ '--tooltip-txt': `'${__('Refresh CRM Related Lists', 'bitwelzp')}'` }} type="button" disabled={isLoading}>&#x21BB;</button>
        <br />
        <br />
      </div>
      <b className="wdt-100 d-in-b">{__('Layout:', 'bitwelzp')}</b>
      <select onChange={handleInput} name="layout" value={crmConf?.relatedlists?.[tab - 1]?.layout} className="btcd-paper-inp w-7" disabled={!crmConf?.relatedlists?.[tab - 1]?.module}>
        <option value="">{__('Select Layout', 'bitwelzp')}</option>
        {
          crmConf?.default?.layouts?.[crmConf.relatedlists?.[tab - 1]?.module] && Object.keys(crmConf.default.layouts[crmConf.relatedlists[tab - 1].module]).map(layoutApiName => (
            <option key={layoutApiName} value={layoutApiName}>
              {layoutApiName}
            </option>
          ))
        }
      </select>
      <button onClick={() => refreshLayouts(tab, formID, crmConf, setCrmConf, setisLoading, setSnackbar)} className="icn-btn sh-sm ml-2 mr-2 tooltip" style={{ '--tooltip-txt': '"Refresh CRM Layouts"' }} type="button" disabled={isLoading}>&#x21BB;</button>
      <br />
      <br />
      {
        crmConf.default?.layouts?.[crmConf?.relatedlists?.[tab - 1]?.module]?.[crmConf?.relatedlists?.[tab - 1]?.layout]?.fields
        && (
          <>
            <div className="mt-4"><b className="wdt-100">{__('Field Map', 'bitwelzp')}</b></div>
            <div className="btcd-hr mt-1" />
            <div className="flx flx-around mt-2 mb-1">
              <div className="txt-dp"><b>{__('Form Fields', 'bitwelzp')}</b></div>
              <div className="txt-dp"><b>{__('Zoho Fields', 'bitwelzp')}</b></div>
            </div>

            {crmConf.relatedlists?.[tab - 1]?.field_map?.map((itm, i) => (
              <ZohoCRMFieldMap
                key={`crm-m-${i + 9}`}
                i={i}
                field={itm}
                crmConf={crmConf}
                formFields={formFields}
                setCrmConf={setCrmConf}
                tab={tab}
                setSnackbar={setSnackbar}
              />
            ))}
            <div className="txt-center  mt-2" style={{ marginRight: 85 }}><button onClick={() => addFieldMap(crmConf.relatedlists[tab - 1].field_map.length, crmConf, setCrmConf, false, tab)} className="icn-btn sh-sm" type="button">+</button></div>
            <br />
            <br />
            {crmConf.default?.layouts[crmConf.relatedlists[tab - 1].module]?.[crmConf.relatedlists[tab - 1].layout] && Object.keys(crmConf.default.layouts[crmConf.relatedlists[tab - 1].module][crmConf.relatedlists[tab - 1].layout].fileUploadFields).length !== 0 && (
              <>
                <div className="mt-4"><b className="wdt-100">{__('File Upload Field Map', 'bitwelzp')}</b></div>
                <div className="btcd-hr mt-1" />
                <div className="flx flx-around mt-2 mb-1">
                  <div className="txt-dp"><b>{__('Form Fields', 'bitwelzp')}</b></div>
                  <div className="txt-dp"><b>{__('Zoho Fields', 'bitwelzp')}</b></div>
                </div>

                {crmConf.relatedlists[tab - 1].upload_field_map.map((itm, i) => (
                  <ZohoCRMFieldMap
                    key={`crm-m-${i + 9}`}
                    i={i}
                    uploadFields={1}
                    field={itm}
                    crmConf={crmConf}
                    formFields={formFields}
                    setCrmConf={setCrmConf}
                    tab={tab}
                    setSnackbar={setSnackbar}
                  />
                ))}
                <div className="txt-center  mt-2" style={{ marginRight: 85 }}><button onClick={() => addFieldMap(crmConf.relatedlists[tab - 1].upload_field_map.length, crmConf, setCrmConf, true, tab)} className="icn-btn sh-sm" type="button">+</button></div>
                <br />
                <br />
              </>
            )}
            <div className="mt-4"><b className="wdt-100">{__('Actions', 'bitwelzp')}</b></div>
            <div className="btcd-hr mt-1" />

            <ZohoCRMActions
              formFields={formFields}
              crmConf={crmConf}
              setCrmConf={setCrmConf}
              tab={tab}
              formID={formID}
              setSnackbar={setSnackbar}
            />
          </>
        )
      }

    </>
  )
}
