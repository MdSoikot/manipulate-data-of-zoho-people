/* eslint-disable no-param-reassign */

import { useState } from 'react'
import MultiSelect from 'react-multiple-select-dropdown-lite'
import { ReactSortable } from 'react-sortablejs'
import 'react-multiple-select-dropdown-lite/dist/index.css'
import { __ } from '../../../Utils/i18nwrap'
import ConfirmModal from '../../Utilities/ConfirmModal'
import CheckBox from '../../Utilities/CheckBox'
import TableCheckBox from '../../Utilities/TableCheckBox'
import Loader from '../../Loaders/Loader'
import Modal from '../../Utilities/Modal'
import TitleModal from '../../Utilities/TitleModal'
import { refreshAssigmentRules, refreshOwners, refreshTags } from './ZohoCRMCommonFunc'

export default function ZohoCRMActions({ crmConf, setCrmConf, formFields, tab, formID, setSnackbar }) {
  const [upsertMdl, setUpsertMdl] = useState(false)
  const [isLoading, setisLoading] = useState(false)
  const [actionMdl, setActionMdl] = useState({ show: false, action: () => { } })
  /* eslint-disable-next-line no-undef */
  const isPro = typeof bitwelzp !== 'undefined' && bitwelzp.isPro

  const actionHandler = (val, typ) => {
    const newConf = { ...crmConf }
    if (tab === 0) {
      if (typ === 'attachment') {
        if (val !== '') {
          newConf.actions.attachment = val
        } else {
          delete newConf.actions.attachment
        }
      }
      if (typ === 'approval') {
        if (val.target.checked) {
          newConf.actions.approval = true
        } else {
          delete newConf.actions.approval
        }
      }
      if (typ === 'workflow') {
        if (val.target.checked) {
          newConf.actions.workflow = true
        } else {
          delete newConf.actions.workflow
        }
      }
      if (typ === 'blueprint') {
        if (val.target.checked) {
          newConf.actions.blueprint = true
        } else {
          delete newConf.actions.blueprint
        }
      }
      if (typ === 'gclid') {
        if (val.target.checked) {
          newConf.actions.gclid = true
        } else {
          delete newConf.actions.gclid
        }
      }
      if (typ === 'assignment_rules') {
        if (val !== '') {
          newConf.actions.assignment_rules = val
        } else {
          delete newConf.actions.assignment_rules
        }
      }
      if (typ === 'tag_rec') {
        if (val !== '') {
          newConf.actions.tag_rec = val
        } else {
          delete newConf.actions.tag_rec
        }
      }
      if (typ === 'rec_owner') {
        if (val !== '') {
          newConf.actions.rec_owner = val
        } else {
          delete newConf.actions.rec_owner
        }
      }
      if (typ === 'upsert') {
        if (val.target.checked) {
          const crmField = newConf.default.layouts[newConf.module][newConf.layout].unique?.map((name, i) => ({ i, name }))
          newConf.actions.upsert = { overwrite: true, crmField }
        } else {
          delete newConf.actions.upsert
        }
      }
    } else {
      if (typ === 'attachment') {
        if (val !== '') {
          newConf.relatedlists[tab - 1].actions.attachment = val
        } else {
          delete newConf.relatedlists[tab - 1].actions.attachment
        }
      }
      if (typ === 'approval') {
        if (val.target.checked) {
          newConf.relatedlists[tab - 1].actions.approval = true
        } else {
          delete newConf.relatedlists[tab - 1].actions.approval
        }
      }
      if (typ === 'workflow') {
        if (val.target.checked) {
          newConf.relatedlists[tab - 1].actions.workflow = true
        } else {
          delete newConf.relatedlists[tab - 1].actions.workflow
        }
      }
      if (typ === 'blueprint') {
        if (val.target.checked) {
          newConf.relatedlists[tab - 1].actions.blueprint = true
        } else {
          delete newConf.relatedlists[tab - 1].actions.blueprint
        }
      }
      if (typ === 'gclid') {
        if (val.target.checked) {
          newConf.relatedlists[tab - 1].actions.gclid = true
        } else {
          delete newConf.relatedlists[tab - 1].actions.gclid
        }
      }
      if (typ === 'assignment_rules') {
        if (val !== '') {
          newConf.relatedlists[tab - 1].actions.assignment_rules = val
        } else {
          delete newConf.relatedlists[tab - 1].actions.assignment_rules
        }
      }
      if (typ === 'tag_rec') {
        if (val !== '') {
          newConf.relatedlists[tab - 1].actions.tag_rec = val
        } else {
          delete newConf.relatedlists[tab - 1].actions.tag_rec
        }
      }
      if (typ === 'rec_owner') {
        if (val !== '') {
          newConf.relatedlists[tab - 1].actions.rec_owner = val
        } else {
          delete newConf.relatedlists[tab - 1].actions.rec_owner
        }
      }
      if (typ === 'upsert') {
        if (val.target.checked) {
          const crmField = newConf.default.layouts[newConf.relatedlists[tab - 1].module][newConf.relatedlists[tab - 1].layout].unique?.map((name, i) => ({ i, name }))
          newConf.relatedlists[tab - 1].actions.upsert = { overwrite: true, crmField }
        } else {
          delete newConf.relatedlists[tab - 1].actions.upsert
        }
      }
    }

    setCrmConf({ ...newConf })
  }

  const clsActionMdl = () => {
    setActionMdl({ show: false })
  }

  const module = tab === 0 ? crmConf.module : crmConf.relatedlists[tab - 1].module
  const getTags = () => {
    const arr = [
      { title: 'Zoho CRM Tags', type: 'group', childs: [] },
      { title: 'Form Fields', type: 'group', childs: [] },
    ]

    if (crmConf.default.tags?.[module]) {
      arr[0].childs = Object.values(crmConf.default.tags?.[module]).map(tagName => ({ label: tagName, value: tagName }))
    }

    arr[1].childs = formFields.map(itm => ({ label: itm.name, value: `\${${itm.key}}` }))
    return arr
  }

  const setUpsertSettings = (val, typ) => {
    const newConf = { ...crmConf }
    if (tab === 0) {
      if (typ === 'list') {
        newConf.actions.upsert.crmField = val
      }
      if (typ === 'overwrite') {
        newConf.actions.upsert.overwrite = val
      }
    } else {
      if (typ === 'list') {
        newConf.relatedlists[tab - 1].actions.upsert.crmField = val
      }
      if (typ === 'overwrite') {
        newConf.relatedlists[tab - 1].actions.upsert.overwrite = val
      }
    }
    setCrmConf({ ...newConf })
  }

  const openRecOwnerModal = () => {
    if (!crmConf.default?.crmOwner) {
      refreshOwners(formID, crmConf, setCrmConf, setisLoading, setSnackbar)
    }
    setActionMdl({ show: 'rec_owner' })
  }

  const openAssignmentRulesModal = () => {
    if (!crmConf?.default?.assignmentRules?.[module]) {
      refreshAssigmentRules(tab, crmConf, setCrmConf, setisLoading, setSnackbar)
    }
    setActionMdl({ show: 'assignment_rules' })
  }

  const openUpsertModal = () => {
    if (tab && !crmConf.relatedlists[tab - 1].actions.upsert?.crmField) {
      const newConf = { ...crmConf }
      const crmField = newConf.default.layouts[newConf.relatedlists[tab - 1].module][newConf.relatedlists[tab - 1].layout].unique?.map((name, i) => ({ i, name }))
      newConf.relatedlists[tab - 1].actions.upsert = { overwrite: true, crmField }
      setCrmConf(newConf)
    } else if (!crmConf.actions.upsert?.crmField) {
      const newConf = { ...crmConf }
      const crmField = newConf.default.layouts[newConf.module][newConf.layout].unique?.map((name, i) => ({ i, name }))
      newConf.actions.upsert = { overwrite: true, crmField }
      setCrmConf({ ...newConf })
    }
    setUpsertMdl(true)
  }

  return (
    <div className="pos-rel">
      {!isPro && (
        <div className="pro-blur flx w-10" style={{ top: -25 }}>
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
      <div className="d-flx flx-wrp">
        <TableCheckBox onChange={(e) => actionHandler(e, 'workflow')} checked={tab === 0 ? 'workflow' in crmConf.actions : 'workflow' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Workflow" title={__('Workflow', 'bitwelzp')} subTitle={__('Trigger CRM workflows', 'bitwelzp')} />
        <TableCheckBox onChange={() => setActionMdl({ show: 'attachment' })} checked={tab === 0 ? 'attachment' in crmConf.actions : 'attachment' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Attachment" title={__('Attachment', 'bitwelzp')} subTitle={__('Add attachments or signatures from BitForm to CRM.', 'bitwelzp')} />
        <TableCheckBox onChange={(e) => actionHandler(e, 'approval')} checked={tab === 0 ? 'approval' in crmConf.actions : 'approval' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Approval" title={__('Approval', 'bitwelzp')} subTitle={__('Send entries to CRM approval list.', 'bitwelzp')} />
        <TableCheckBox onChange={(e) => actionHandler(e, 'blueprint')} checked={tab === 0 ? 'blueprint' in crmConf.actions : 'blueprint' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Blueprint" title={__('Blueprint', 'bitwelzp')} subTitle={__('Trigger CRM Blueprint', 'bitwelzp')} />
        <TableCheckBox onChange={(e) => actionHandler(e, 'gclid')} checked={tab === 0 ? 'gclid' in crmConf.actions : 'gclid' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Capture_GCLID" title={__('Capture GCLID', 'bitwelzp')} subTitle={__('Sends the click details of AdWords Ads to Zoho CRM.', 'bitwelzp')} />
        <TitleModal action={openUpsertModal}>
          <TableCheckBox onChange={(e) => actionHandler(e, 'upsert')} checked={tab === 0 ? 'upsert' in crmConf.actions : 'upsert' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Upsert_Record" title={__('Upsert Records', 'bitwelzp')} subTitle={__('The record is updated if it already exists else it is inserted as a new record.', 'bitwelzp')} />
        </TitleModal>
        <TableCheckBox onChange={openAssignmentRulesModal} checked={tab === 0 ? 'assignment_rules' in crmConf.actions : 'assignment_rules' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Assignment_Rule" title={__('Assignment Rules', 'bitwelzp')} subTitle={__('Trigger Assignment Rules in Zoho CRM.', 'bitwelzp')} />
        <TableCheckBox onChange={() => setActionMdl({ show: 'tag_rec' })} checked={tab === 0 ? 'tag_rec' in crmConf.actions : 'tag_rec' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Tag_Records" title={__('Tag Records', 'bitwelzp')} subTitle={__('Add a tag to records pushed to Zoho CRM.', 'bitwelzp')} />
        <TableCheckBox onChange={openRecOwnerModal} checked={tab === 0 ? 'rec_owner' in crmConf.actions : 'rec_owner' in crmConf.relatedlists[tab - 1].actions} className="wdt-200 mt-4 mr-2" value="Record_Owner" title={__('Record Owner', 'bitwelzp')} subTitle={__('Add a owner to records pushed to Zoho CRM.', 'bitwelzp')} />
      </div>

      <ConfirmModal
        className="custom-conf-mdl"
        mainMdlCls="o-v"
        btnClass="blue"
        btnTxt={__('Ok', 'bitwelzp')}
        show={actionMdl.show === 'attachment'}
        close={clsActionMdl}
        action={clsActionMdl}
        title={__('Select Attachment', 'bitwelzp')}
      >
        <div className="btcd-hr mt-2" />
        <div className="mt-2">{__('Select file upload fields', 'bitwelzp')}</div>
        <MultiSelect
          defaultValue={tab === 0 ? crmConf.actions.attachment : crmConf.relatedlists[tab - 1].actions.attachment}
          className="mt-2 w-9"
          onChange={(val) => actionHandler(val, 'attachment')}
          options={formFields.filter(itm => (itm.type === 'file')).map(itm => ({ label: itm.name, value: itm.key }))}
        />
      </ConfirmModal>

      <ConfirmModal
        className="custom-conf-mdl"
        mainMdlCls="o-v"
        btnClass="blue"
        btnTxt={__('Ok', 'bitwelzp')}
        show={actionMdl.show === 'assignment_rules'}
        close={clsActionMdl}
        action={clsActionMdl}
        title={__('Assignment Rules', 'bitwelzp')}
      >
        <div className="btcd-hr mt-2" />
        <div className="mt-2">{__('Assignment Rules', 'bitwelzp')}</div>
        {isLoading
          ? (
            <Loader style={{
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              height: 45,
              transform: 'scale(0.5)',
            }}
            />
          )
          : (
            <div className="flx flx-between mt-2">
              <select
                value={tab === 0 ? crmConf.actions.assignment_rules : crmConf.relatedlists[tab - 1].actions.assignment_rules}
                className="btcd-paper-inp"
                onChange={e => actionHandler(e.target.value, 'assignment_rules')}
              >
                <option value="">{__('Select Assignment Rule', 'bitwelzp')}</option>
                {crmConf?.default?.assignmentRules?.[module] && Object.keys(crmConf.default.assignmentRules[module]).map(assignmentName => <option key={assignmentName} value={crmConf.default.assignmentRules[module][assignmentName]}>{assignmentName}</option>)}
              </select>
              <button onClick={() => refreshAssigmentRules(tab, crmConf, setCrmConf, setisLoading, setSnackbar)} className="icn-btn sh-sm ml-2 mr-2 tooltip" style={{ '--tooltip-txt': `'${__('Refresh CRM Assignment Rules', 'bitwelzp')}'` }} type="button" disabled={isLoading}>&#x21BB;</button>
            </div>
          )}
      </ConfirmModal>

      <ConfirmModal
        className="custom-conf-mdl"
        mainMdlCls="o-v"
        btnClass="blue"
        btnTxt={__('Ok', 'bitwelzp')}
        show={actionMdl.show === 'tag_rec'}
        close={clsActionMdl}
        action={clsActionMdl}
        title={__('Tag Records', 'bitwelzp')}
      >
        <div className="btcd-hr mt-2 mb-2" />
        <small>{__('Add a tag to records pushed to Zoho CRM', 'bitwelzp')}</small>
        <div className="mt-2">{__('Tag Name', 'bitwelzp')}</div>
        {isLoading
          ? (
            <Loader style={{
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              height: 45,
              transform: 'scale(0.5)',
            }}
            />
          )
          : (
            <div className="flx flx-between mt-2">
              <MultiSelect
                className="msl-wrp-options"
                defaultValue={tab === 0 ? crmConf.actions.tag_rec : crmConf.relatedlists[tab - 1].actions.tag_rec}
                options={getTags()}
                onChange={(val) => actionHandler(val, 'tag_rec')}
                customValue
              />
              <button onClick={() => refreshTags(tab, formID, crmConf, setCrmConf, setisLoading, setSnackbar)} className="icn-btn sh-sm ml-2 mr-2 tooltip" style={{ '--tooltip-txt': `${__('Refresh CRM Tags', 'bitwelzp')}'` }} type="button" disabled={isLoading}>&#x21BB;</button>
            </div>
          )}

      </ConfirmModal>

      <ConfirmModal
        className="custom-conf-mdl"
        mainMdlCls="o-v"
        btnClass="blue"
        btnTxt={__('Ok', 'bitwelzp')}
        show={actionMdl.show === 'rec_owner'}
        close={clsActionMdl}
        action={clsActionMdl}
        title={__('Record Owner', 'bitwelzp')}
      >
        <div className="btcd-hr mt-2" />
        <div className="mt-2">{__('Owner Name', 'bitwelzp')}</div>
        {isLoading
          ? (
            <Loader style={{
              display: 'flex',
              justifyContent: 'center',
              alignItems: 'center',
              height: 45,
              transform: 'scale(0.5)',
            }}
            />
          )
          : (
            <div className="flx flx-between mt-2">
              <select
                value={tab === 0 ? crmConf.actions.rec_owner : crmConf.relatedlists[tab - 1].actions.rec_owner}
                className="btcd-paper-inp"
                onChange={e => actionHandler(e.target.value, 'rec_owner')}
              >
                <option value="">{__('Select Owner', 'bitwelzp')}</option>
                {crmConf.default?.crmOwner && Object.values(crmConf.default.crmOwner)?.map(owner => <option key={owner.id} value={owner.id}>{owner.full_name}</option>)}
              </select>
              <button onClick={() => refreshOwners(formID, crmConf, setCrmConf, setisLoading, setSnackbar)} className="icn-btn sh-sm ml-2 mr-2 tooltip" style={{ '--tooltip-txt': `'${__('Refresh CRM Owners', 'bitwelzp')}'` }} type="button" disabled={isLoading}>&#x21BB;</button>
            </div>
          )}
      </ConfirmModal>

      <Modal
        md
        show={upsertMdl}
        setModal={setUpsertMdl}
        title={__('Upsert Record', 'bitwelzp')}
      >
        <div className="o-a">
          {
            tab === 0
              ? crmConf?.actions?.upsert && (
                <>
                  <div className="font-w-m mt-2">{__('Upsert Using', 'bitwelzp')}</div>
                  <small>{__('Arrange fields in order of preferance for upsertion', 'bitwelzp')}</small>
                  <ReactSortable list={crmConf.actions.upsert?.crmField} setList={l => setUpsertSettings(l, 'list')}>
                    {crmConf.actions.upsert?.crmField?.map((itm) => (
                      <div key={`cf-${itm.i}`} className="upsert_rec w-7 mt-1 flx">
                        <span className="btcd-icn btcd-mnu mr-2" />
                        {itm.name}
                      </div>
                    ))}
                  </ReactSortable>

                  <div className="font-w-m mt-3">{__('Upsert Preferance', 'bitwelzp')}</div>
                  <small>{__('Overwrite existing field values in Zoho CRM with empty field values from Zoho CRM For Fluent Form while upserting a record?', 'bitwelzp')}</small>
                  <div>
                    <CheckBox onChange={() => setUpsertSettings(true, 'overwrite')} radio checked={crmConf.actions.upsert?.overwrite} name="up-rec" title={__('Yes', 'bitwelzp')} />
                    <CheckBox onChange={() => setUpsertSettings(false, 'overwrite')} radio checked={!crmConf.actions.upsert?.overwrite} name="up-rec" title={__('No', 'bitwelzp')} />
                  </div>
                </>
              )
              : crmConf?.relatedlists[tab - 1]?.actions?.upsert && (
                <>
                  <div className="font-w-m mt-2">{__('Upsert Using', 'bitwelzp')}</div>
                  <small>{__('Arrange fields in order of preferance for upsertion', 'bitwelzp')}</small>
                  <ReactSortable list={crmConf.relatedlists[tab - 1].actions.upsert?.crmField} setList={l => setUpsertSettings(l, 'list')}>
                    {crmConf.relatedlists[tab - 1].actions.upsert?.crmField?.map((itm) => (
                      <div key={`cf-${itm.i}`} className="upsert_rec w-7 mt-1 flx">
                        <span className="btcd-icn btcd-mnu mr-2" />
                        {itm.name}
                      </div>
                    ))}
                  </ReactSortable>

                  <div className="font-w-m mt-3">{__('Upsert Preferance', 'bitwelzp')}</div>
                  <small>{__('Overwrite existing field values in Zoho CRM with empty field values from Zoho CRM For Fluent Form while upserting a record?', 'bitwelzp')}</small>
                  <div>
                    <CheckBox onChange={() => setUpsertSettings(true, 'overwrite')} radio checked={crmConf.relatedlists[tab - 1].actions.upsert?.overwrite} name="up-rec" title={__('Yes', 'bitwelzp')} />
                    <CheckBox onChange={() => setUpsertSettings(false, 'overwrite')} radio checked={!crmConf.relatedlists[tab - 1].actions.upsert?.overwrite} name="up-rec" title={__('No', 'bitwelzp')} />
                  </div>
                </>
              )
          }
        </div>
      </Modal>
    </div>
  )
}
