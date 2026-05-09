import type { SVGAttributes } from 'react';

export default function AppLogoIcon(props: SVGAttributes<SVGElement>) {
    return (
        <svg {...props} viewBox="0 0 56 56" xmlns="http://www.w3.org/2000/svg">
            <rect
                x="12"
                y="10"
                width="18"
                height="30"
                rx="2.5"
                fill="white"
                opacity="0.18"
            />
            <rect
                x="12"
                y="10"
                width="18"
                height="30"
                rx="2.5"
                fill="none"
                stroke="white"
                strokeWidth="1.8"
            />
            <line
                x1="12"
                y1="40"
                x2="30"
                y2="40"
                stroke="white"
                strokeWidth="1.8"
                strokeLinecap="round"
            />
            <circle cx="26.5" cy="25" r="2" fill="white" />
            <g transform="translate(38,38) rotate(-45)">
                <rect
                    x="-9"
                    y="-3.5"
                    width="17"
                    height="7"
                    rx="2"
                    fill="white"
                />
                <polygon points="-9,-3.5 -14,0 -9,3.5" fill="white" />
                <rect
                    x="7"
                    y="-2"
                    width="3.5"
                    height="4"
                    rx="1"
                    fill="#378ADD"
                />
            </g>
        </svg>
    );
}
