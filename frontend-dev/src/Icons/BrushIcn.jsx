export default function BrushIcn({ stroke = 2, height = 17, width = 13 }) {
  return (
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 145 205" height={height} width={width}>
      <defs />
      <g stroke="currentColor" strokeMiterlimit="10" strokeWidth={Number(stroke) * 8} clipPath="url(#clip0)">
        <path d="M136.7 64.7H8h128.7zM14.1 14.9A11.2 11.2 0 008 24.8v66.7a29.6 29.6 0 0024.3 29.1l13.6 2.5a15.4 15.4 0 0112.5 18.2l-5.7 27c-1.2 5.7.3 11.7 4.2 16a20.9 20.9 0 0030.9 0 18.2 18.2 0 004.2-16l-5.7-27.1A15.4 15.4 0 0198.8 123l13.6-2.4a29.4 29.4 0 0024.3-29.1V18.4c0-3.3-3.5-5.3-6.4-3.8A62.5 62.5 0 0183.6 20c-3.6-1-7-2.4-10.2-4.1a64.5 64.5 0 00-59.3-1z" />
      </g>
      <defs>
        <clipPath id="clip0">
          <path fill="#fff" d="M0 0h144.7v199.1H0z" transform="rotate(180 72.3 99.5)" />
        </clipPath>
      </defs>
    </svg>

  )
}
