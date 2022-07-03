/* eslint-disable jsx-a11y/control-has-associated-label */
import { useState } from 'react'
import { useFela } from 'react-fela'
import { useRecoilValue } from 'recoil'
import { $breakpoint } from '../GlobalStates'
import AddIcon from '../Icons/AddIcon'
import EllipsisIcon from '../Icons/EllipsisIcon'
import LaptopIcn from '../Icons/LaptopIcn'
import MobileIcon from '../Icons/MobileIcon'
import TabletIcon from '../Icons/TabletIcon'
import ut from '../styles/2.utilities'
import OptionToolBarStyle from '../styles/OptionToolbar.style'
import { __ } from '../Utils/i18nwrap'
import FormBuilderHistory from './FormBuilderHistory'
import Downmenu from './Utilities/Downmenu'
import Tip from './Utilities/Tip'

export default function OptionToolBar({ setResponsiveView }) {
    const { css } = useFela()
    const breakpoint = useRecoilValue($breakpoint)
    const [responsiveMenu, setResponsiveMenu] = useState(false)
    return (
        <div className={css(OptionToolBarStyle.optionToolBar)}>
            <div className={css(OptionToolBarStyle.form_section)}>
                <div className={`${css(OptionToolBarStyle.field_btn)} __active`}>
                    <AddIcon size="25" />
                    <span className={css(OptionToolBarStyle.txt)}>Form Fields</span>
                </div>
                <div className={css(OptionToolBarStyle.option_section)}>
                    <Tip msg="Large Screen View">
                        <button onClick={() => setResponsiveView('lg')} className={`${css([OptionToolBarStyle.icn_btn, ut.icn_hover])} ${breakpoint === 'lg' && 'active'}`} type="button"><LaptopIcn size={29} stroke={1.6} /></button>
                    </Tip>
                    <Tip msg="Medium Screen View">
                        <button onClick={() => setResponsiveView('md')} className={`${css([OptionToolBarStyle.icn_btn, ut.icn_hover])} ${breakpoint === 'md' && 'active'}`} type="button"><TabletIcon size={22} /></button>
                    </Tip>
                    <Tip msg="Small Screen View">
                        <button onClick={() => setResponsiveView('sm')} className={`${css([OptionToolBarStyle.icn_btn, ut.icn_hover])} ${breakpoint === 'sm' && 'active'}`} type="button"><MobileIcon size={23} /></button>
                    </Tip>
                    <Downmenu
                        place="bottom-end"
                        onShow={() => setResponsiveMenu(true)}
                        onHide={() => setResponsiveMenu(false)}
                    >
                        <button className={`${css([OptionToolBarStyle.icn_btn, ut.icn_hover])} ${responsiveMenu ? 'active' : ''}`} type="button"><EllipsisIcon size="38" /></button>
                        <div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                            <div>  <button>sdas</button></div>
                        </div>
                    </Downmenu>
                    <div className={css(OptionToolBarStyle.border_right)} />
                    <FormBuilderHistory />
                </div>
            </div>
            <div className="theme-section" />
        </div>
    )
}
