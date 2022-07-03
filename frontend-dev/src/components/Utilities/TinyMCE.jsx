/* eslint-disable no-undef */
import { useEffect } from 'react'
import { __ } from '../../Utils/i18nwrap'

export default function TinyMCE({ formFields, id, value, onChangeHandler, toolbarMnu, height, width }) {
  useEffect(() => {
    window.tinymce && tinymce.remove()
    return () => window.tinymce && tinymce.remove()
  }, [])

  useEffect(() => {
    window.tinymce && tinymce.remove()
    timyMceInit()
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [formFields, id])

  const timyMceInit = () => {
    if (typeof tinymce !== 'undefined' && (!formFields || formFields?.length > 0)) {
      const s = document.querySelectorAll('.form-fields-em')
      for (let i = 0; i < s.length; i += 1) {
        s[i].style.display = 'none'
      }
      // eslint-disable-next-line no-undef
      tinymce.init({
        selector: `textarea#${id}-settings`,
        height: height || 150,
        width: width || '100%',
        branding: false,
        resize: 'verticle',
        convert_urls: false,
        theme: 'modern',
        plugins: 'directionality fullscreen image link media charmap hr lists textcolor colorpicker wordpress',
        toolbar: toolbarMnu || 'formatselect | fontsizeselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat toogleCode wp_code| addFormField',
        image_advtab: true,
        default_link_target: '_blank',
        setup(editor) {
          editor.on('Paste Change input Undo Redo', () => {
            onChangeHandler(editor.getContent())
          })

          formFields && editor.addButton('addFormField', {
            text: 'Form Fields ',
            tooltip: 'Add Form Field Value in Message',
            type: 'menubutton',
            icon: false,
            menu: formFields?.map(i => !i.type.match(/^(file-up|recaptcha)$/) && ({ text: i.name, onClick() { editor.insertContent(`\${${i.key}}`) } })),
          })

          editor.addButton('toogleCode', {
            text: '</>',
            tooltip: __('Toggle preview', 'bitwelzp'),
            icon: false,
            onclick(e) {
              const { $ } = e.control
              const myTextarea = $(`#${id}-settings`)
              const myIframe = $(editor.iframeElement)
              myTextarea.value = editor.getContent({ source_view: true })
              myIframe.toggleClass('hidden')
              myTextarea.toggleClass('visible')
              if ($('iframe.hidden').length > 0) {
                myTextarea.prependTo('.mce-edit-area')
              } else {
                editor.setContent(document.getElementById(`${id}-settings`).value)
              }
            },
          })
        },
      })
    }
  }

  return (
    <textarea
      id={`${id}-settings`}
      className="btcd-paper-inp mt-1"
      rows="5"
      value={value}
      onChange={(ev) => onChangeHandler(ev.target.value)}
      style={{ width: '95.5%', height: 'auto' }}
    />
  )
}
