import type { Metadata } from 'next'
import { Orbitron, Russo_One, Audiowide, Poppins, Rajdhani } from 'next/font/google'
import './globals.css'

const orbitron = Orbitron({ 
  subsets: ['latin'],
  variable: '--font-orbitron',
  display: 'swap',
})

const russoOne = Russo_One({ 
  weight: '400',
  subsets: ['latin'],
  variable: '--font-russo',
  display: 'swap',
})

const audiowide = Audiowide({ 
  weight: '400',
  subsets: ['latin'],
  variable: '--font-audiowide',
  display: 'swap',
})

const poppins = Poppins({ 
  weight: ['300', '400', '500', '600', '700'],
  subsets: ['latin'],
  variable: '--font-poppins',
  display: 'swap',
})

const rajdhani = Rajdhani({ 
  weight: ['300', '400', '500', '600', '700'],
  subsets: ['latin'],
  variable: '--font-rajdhani',
  display: 'swap',
})

export const metadata: Metadata = {
  title: 'DIU ESPORTS COMMUNITY - Professional Esports Portfolio',
  description: 'Official portfolio website for DIU Esports Community. Discover our tournaments, achievements, and gaming excellence.',
  keywords: 'DIU, Esports, Gaming, Tournaments, Community, Portfolio',
  icons: {
    icon: '/favicon.ico',
  },
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en" className={`${orbitron.variable} ${russoOne.variable} ${audiowide.variable} ${poppins.variable} ${rajdhani.variable}`}>
      <body className="bg-dark text-white font-poppins overflow-x-hidden">
        {children}
      </body>
    </html>
  )
}
