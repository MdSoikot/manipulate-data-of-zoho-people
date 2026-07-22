/* eslint-disable react/jsx-props-no-spreading */
export default function EyeToggle(props) {
  const id = 'id' in props ? props.id : Math.random()

  return (
    <label htmlFor={`btcd-eye-t-${id}`} className="btcd-eye-t">
      <input id={`btcd-eye-t-${id}`} {...props.props} type="checkbox" className="btcd-cbx-invisible btcd-eye-t-i" />
      <span>
        <span className="btcd-icn icn-remove_red_eye eye-t-v" />
        <span className="btcd-icn icn-visibility_off eye-t-h" />
      </span>
      {' '}
      {props.title}
    </label>
  )
}
