import ContentLoader from 'react-content-loader'

const GridLayoutLoader = () => {
  const width = window.innerWidth - 480
  return (
    <ContentLoader
      speed={0.5}
      width="100%"
      height={430}
      viewBox="0 0 1000 430"
      backgroundColor="#f3f3f3"
      foregroundColor="#ecebeb"
    >
      <rect x="10" y="10" rx="0" ry="0" width={width - 555} height="80" />
      <rect x="521" y="10" rx="0" ry="0" width={width - 530} height="80" />
      <rect x="10" y="100" rx="0" ry="0" width={(width * 62.7) / 100} height="80" />
      <rect x="680" y="100" rx="0" ry="0" width={(width * 35) / 100} height="80" />
      <rect x="10" y="193" rx="0" ry="0" width={(width * 40) / 100} height="80" />
      <rect x="440" y="193" rx="0" ry="0" width={(width * 57.7) / 100} height="80" />
      <rect x="10" y="283" rx="0" ry="0" width={(width * 23.5) / 100} height="80" />
      <rect x="265" y="283" rx="0" ry="0" width={(width * 74.3) / 100} height="80" />

      <rect x="870" y="389" rx="5" ry="5" width="143" height="36" />
      <rect x="725" y="389" rx="5" ry="5" width="122" height="36" />
    </ContentLoader>
  )
}

export default GridLayoutLoader
