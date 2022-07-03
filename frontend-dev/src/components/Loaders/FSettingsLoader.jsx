import ContentLoader from 'react-content-loader'

function FSettingsLoader() {
  return (
    <ContentLoader
      speed={0.5}
      width={800}
      height={330}
      viewBox="0 0 800 330"
      backgroundColor="#f7f7f7"
      foregroundColor="#ecebeb"
    >
      <rect x="2" y="8" rx="5" ry="5" width="127" height="33" />
      <rect x="2" y="68" rx="9" ry="9" width="631" height="48" />
      <rect x="2" y="132" rx="9" ry="9" width="631" height="48" />
      <rect x="2" y="200" rx="9" ry="9" width="631" height="48" />
      <rect x="2" y="261" rx="9" ry="9" width="631" height="48" />
    </ContentLoader>
  )
}

export default FSettingsLoader
