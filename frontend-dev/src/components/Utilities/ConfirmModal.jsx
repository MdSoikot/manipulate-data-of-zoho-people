import { __ } from '../../Utils/i18nwrap'
import LoaderSm from '../Loaders/LoaderSm'
import Modal from './Modal'

function ConfirmModal({ close, action, mainMdlCls, show, btnTxt, body, btn2Txt, btn2Action, btnClass, title, className, children, warning, loading }) {
  return (
    <Modal
      sm
      show={show}
      setModal={close}
      className={mainMdlCls}
      title={title || 'Confirmation'}
      warning={warning || false}
    >
      <div className={`txt-center atn-btns flx flx-center ${className || 'flx-col'}`}>
        <div className={`content ${!className && 'confirm-content'}`}>
          {body}
          {children}
        </div>
        <div className={`txt-center ${warning && 'mt-3'}`}>
          {!btn2Txt && <button onClick={close} disabled={loading} className={`btn btcd-btn-o-gray green w-4 mr-2 br-50 ${!className && 'btn-lg'}`} type="button">{__('Cancel', 'bitwelzp')}</button>}
          {btn2Txt && <button onClick={btn2Action} disabled={loading} className={`btn green w-4 mr-2 br-50 ${!className && 'btn-lg'}`} type="button">{btn2Txt}</button>}
          <button
            onClick={action}
            disabled={loading}
            className={`btn ${btnClass || 'red'} w-4 br-50 ${!className && 'btn-lg'}`}
            style={{ display: 'inline-flex', alignItems: 'center', justifyContent: 'center', gap: 6 }}
            type="button"
          >
            {btnTxt}
            {loading && <LoaderSm size="16" clr="#fff" />}
          </button>
        </div>
      </div>
    </Modal>

  )
}

export default ConfirmModal
