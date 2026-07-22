/* eslint-disable react/jsx-props-no-spreading */
import { useState, useRef, memo } from 'react'
import { CSSTransition } from 'react-transition-group'
import EditIcn from '../../Icons/EditIcn'
import Button from './Button'

function Accordions({ title, customTitle, subtitle, children, titleEditable, onTitleChange, cls, notScroll, header, onExpand, onCollapse }) {
  console.log('%c $render Accordions', 'background:aquamarine;padding:3px;border-radius:5px;')

  const [tgl, setTgl] = useState(false)
  const [H, setH] = useState(0)
  const inp = useRef(null)

  const handleTgl = e => {
    if (!e.target.classList.contains('edit')) {
      setTgl(!tgl)
    }
  }

  const focusEdit = () => {
    inp.current.focus()
  }

  const onAccordionExpand = () => {
    setH('auto')
    if (onExpand) {
      onExpand()
    }
  }

  const onAccordionCollapse = el => {
    setH(el.offsetHeight)
    if (onCollapse) {
      onCollapse()
    }
  }

  return (
    <div className={`btcd-accr sh-sm ${cls}`}>
      <div className={`btcd-accr-btn ${tgl && 'blue'} flx flx-between`} onClick={handleTgl} onKeyPress={handleTgl} role="button" tabIndex={0}>
        <div className="btcd-accr-title w-10">
          <div>
            {customTitle}
            {title !== undefined && <input title={title} ref={inp} className={titleEditable && 'edit'} style={{ color: tgl ? 'white' : 'inherit' }} type="text" onChange={onTitleChange} value={title} readOnly={titleEditable === undefined} />}
            {titleEditable && <div className="edit-icn" onClick={focusEdit} onKeyPress={focusEdit} role="button" tabIndex={0} aria-label="focus edit"><span style={{ color: tgl ? 'white' : 'gray' }}><EditIcn /></span></div>}
            {!tgl && header}
          </div>
          {subtitle !== undefined && <small>{subtitle}</small>}
        </div>

        <Button icn>
          <span className={`btcd-icn icn-${tgl ? 'keyboard_arrow_up' : 'keyboard_arrow_down'}`} style={{ color: tgl ? 'white' : 'inherit' }} />
        </Button>
      </div>

      <div className={`o-h ${tgl && 'delay-overflow'}`} style={{ height: H, transition: 'height 300ms' }}>
        <CSSTransition
          in={tgl}
          timeout={300}
          onEntering={el => setH(el.offsetHeight)}
          onEntered={onAccordionExpand}
          onExiting={() => setH(0)}
          onExit={el => onAccordionCollapse(el)}
          unmountOnExit
        >
          <div className="p-2">
            {children}
          </div>
        </CSSTransition>
      </div>

    </div>
  )
}

export default memo(Accordions)
