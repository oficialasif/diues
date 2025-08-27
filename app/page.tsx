'use client'

import { useEffect } from 'react'
import HeroSection from '@/components/HeroSection'


import GamesPortfolio from '@/components/GamesPortfolio'
import EventsNews from '@/components/EventsNews'
import Leadership from '@/components/Leadership'
import Gallery from '@/components/Gallery'

import Sponsors from '@/components/Sponsors'
import ContactFooter from '@/components/ContactFooter'
import ParticleBackground from '@/components/ParticleBackground'

export default function Home() {
  useEffect(() => {
    // Initialize GSAP and other animations when component mounts
    const initAnimations = async () => {
      const { gsap } = await import('gsap')
      const { ScrollTrigger } = await import('gsap/ScrollTrigger')
      
      gsap.registerPlugin(ScrollTrigger)
      
      // Global GSAP animations
      gsap.fromTo('.fade-in-section', 
        { opacity: 0, y: 50 },
        { 
          opacity: 1, 
          y: 0, 
          duration: 1,
          stagger: 0.2,
          scrollTrigger: {
            trigger: '.fade-in-section',
            start: 'top 80%',
            end: 'bottom 20%',
            toggleActions: 'play none none reverse'
          }
        }
      )
    }

    initAnimations()
  }, [])

  return (
    <main className="min-h-screen bg-dark">
      <ParticleBackground />
      
      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <HeroSection />
      </section>





      {/* Games & Tournament Portfolio */}
      <section id="games" className="relative py-20 bg-dark-secondary">
        <GamesPortfolio />
      </section>

      {/* Events & News */}
      <section className="relative py-20 bg-dark">
        <EventsNews />
      </section>

      {/* Leadership Committee */}
      <section className="relative py-20 bg-dark-secondary">
        <Leadership />
      </section>

      {/* Gallery */}
      <section id="gallery" className="relative py-20 bg-dark">
        <Gallery />
      </section>



      {/* Sponsors & Partners */}
      <section className="relative py-20 bg-dark">
        <Sponsors />
      </section>

      {/* Contact & Footer */}
      <section className="relative py-20 bg-dark-secondary">
        <ContactFooter />
      </section>
    </main>
  )
}
