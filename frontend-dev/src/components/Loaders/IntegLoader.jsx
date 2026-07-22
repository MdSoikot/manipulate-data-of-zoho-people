import ContentLoader from 'react-content-loader'

export default function IntegLoader() {
  return (
    <ContentLoader
      speed={0.5}
      width={800}
      height={270}
      viewBox="0 0 800 270"
      backgroundColor="#f7f7f7"
      foregroundColor="#ecebeb"
    >
      <rect x="0" y="8" rx="5" ry="5" width="153" height="32" />
      <rect x="1" y="64" rx="16" ry="16" width="166" height="200" />
      <rect x="184" y="64" rx="16" ry="16" width="166" height="200" />
      <rect x="366" y="62" rx="16" ry="16" width="166" height="200" />
      <rect x="549" y="62" rx="16" ry="16" width="166" height="200" />
    </ContentLoader>
  )
}
