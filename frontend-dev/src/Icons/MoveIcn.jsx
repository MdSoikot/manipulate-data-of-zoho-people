export default function MoveIcn({ size, stroke = 3 }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" width={size} height={size} viewBox="0 0 30 30">
      <polyline className="svg-icn" strokeWidth={stroke} points="23.31 12.31 26 15 23.31 17.69 26 15 4 15 6.69 17.69 4 15 6.69 12.31" />
      <polyline className="svg-icn" strokeWidth={stroke} points="17.69 23.31 15 26 12.31 23.31 15 26 15 4 12.31 6.69 15 4 17.69 6.69" />
    </svg>
  )
}
