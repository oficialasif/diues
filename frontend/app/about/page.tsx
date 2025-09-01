'use client'

import { useEffect } from 'react'
import { motion } from 'framer-motion'
import { Users, Trophy, Gamepad2, Heart, Target, Zap, Star, Calendar } from 'lucide-react'
import ParticleBackground from '@/components/ParticleBackground'

export default function AboutPage() {
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

  const stats = [
    {
      icon: <Trophy className="w-8 h-8" />,
      value: 35,
      suffix: '+',
      label: 'Tournaments',
      color: 'neon-green',
      delay: 0.1,
    },
    {
      icon: <Users className="w-8 h-8" />,
      value: 2000,
      suffix: '+',
      label: 'Players',
      color: 'primary-blue',
      delay: 0.2,
    },
    {
      icon: <Gamepad2 className="w-8 h-8" />,
      value: 7,
      suffix: '+',
      label: 'Games',
      color: 'cyber-neon-purple',
      delay: 0.3,
    },
    {
      icon: <Heart className="w-8 h-8" />,
      value: 150,
      suffix: '+',
      label: 'Winners',
      color: 'cyber-neon-pink',
      delay: 0.4,
    },
    {
      icon: <Target className="w-8 h-8" />,
      value: 95,
      suffix: '%',
      label: 'Success Rate',
      color: 'neon-green',
      delay: 0.5,
    },
    {
      icon: <Zap className="w-8 h-8" />,
      value: 24,
      suffix: '/7',
      label: 'Active',
      color: 'primary-blue',
      delay: 0.6,
    },
    {
      icon: <Star className="w-8 h-8" />,
      value: 4.9,
      suffix: '',
      label: 'Rating',
      color: 'cyber-neon-purple',
      delay: 0.7,
    },
    {
      icon: <Calendar className="w-8 h-8" />,
      value: 365,
      suffix: '',
      label: 'Days Active',
      color: 'cyber-neon-pink',
      delay: 0.8,
    },
  ]

  const getColorClasses = (color: string) => {
    const colorMap: { [key: string]: string } = {
      'neon-green': 'border-neon-green text-neon-green bg-neon-green bg-opacity-10',
      'primary-blue': 'border-primary-blue text-primary-blue bg-primary-blue bg-opacity-10',
      'cyber-neon-purple': 'border-cyber-neon-purple text-cyber-neon-purple bg-cyber-neon-purple bg-opacity-10',
      'cyber-neon-pink': 'border-cyber-neon-pink text-cyber-neon-pink bg-cyber-neon-pink bg-opacity-10',
    }
    return colorMap[color] || colorMap['neon-green']
  }

  return (
    <main className="min-h-screen bg-dark">
      <ParticleBackground />
      
      {/* Hero Section */}
      <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
        <div className="absolute inset-0 cyber-grid opacity-20"></div>
        
        <div className="relative z-10 text-center px-4 max-w-6xl mx-auto">
          <motion.h1
            initial={{ y: 50, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ duration: 0.8 }}
            className="text-6xl md:text-8xl font-orbitron font-bold mb-6 neon-text glitch-text"
            data-text="ABOUT DIU ESPORTS"
          >
            ABOUT DIU ESPORTS
          </motion.h1>
          
          <motion.p
            initial={{ y: 50, opacity: 0 }}
            animate={{ y: 0, opacity: 1 }}
            transition={{ duration: 0.8, delay: 0.3 }}
            className="text-2xl md:text-3xl font-audiowide text-neon mb-8 max-w-4xl mx-auto"
          >
            Building the future of university esports, one champion at a time
          </motion.p>
        </div>
      </section>

      {/* About Content */}
      <section className="relative py-20 bg-dark-secondary">
        <div className="container mx-auto px-4">
          <div className="grid lg:grid-cols-2 gap-16 items-center">
            {/* Left Column - Text Content */}
            <motion.div
              initial={{ opacity: 0, x: -50 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              viewport={{ once: true }}
              className="space-y-8"
            >
              <div>
                <h2 className="text-4xl md:text-5xl font-audiowide text-white mb-6 neon-text">
                  Our Story
                </h2>
                <p className="text-lg text-gray-300 font-poppins leading-relaxed mb-6">
                  Founded in 2020, DIU Esports Community has grown from a small group of passionate gamers 
                  to one of the most competitive university esports programs in Bangladesh. We believe that 
                  gaming is not just entertainment, but a platform for developing strategic thinking, teamwork, 
                  and leadership skills.
                </p>
                <p className="text-lg text-gray-300 font-poppins leading-relaxed">
                  Our community brings together students from all backgrounds who share a common passion for 
                  competitive gaming. Whether you're a casual player or aspiring professional, there's a place 
                  for you in our growing family.
                </p>
              </div>

              <div>
                <h3 className="text-3xl font-audiowide text-neon mb-4">
                  Our Mission
                </h3>
                <p className="text-lg text-gray-300 font-poppins leading-relaxed">
                  To create an inclusive, competitive, and supportive environment where students can develop 
                  their gaming skills, build lasting friendships, and represent DIU with pride in national 
                  and international esports competitions.
                </p>
              </div>

              <div>
                <h3 className="text-3xl font-audiowide text-neon mb-4">
                  What We Offer
                </h3>
                <ul className="space-y-3 text-gray-300 font-poppins">
                  <li className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-neon rounded-full"></div>
                    Regular tournaments and competitions
                  </li>
                  <li className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-neon rounded-full"></div>
                    Professional coaching and training
                  </li>
                  <li className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-neon rounded-full"></div>
                    Networking with industry professionals
                  </li>
                  <li className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-neon rounded-full"></div>
                    Access to gaming equipment and facilities
                  </li>
                  <li className="flex items-center gap-3">
                    <div className="w-2 h-2 bg-neon rounded-full"></div>
                    Scholarship opportunities for top performers
                  </li>
                </ul>
              </div>
            </motion.div>

            {/* Right Column - Stats Grid */}
            <motion.div
              initial={{ opacity: 0, x: 50 }}
              whileInView={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8, delay: 0.2 }}
              viewport={{ once: true }}
              className="grid grid-cols-2 gap-6"
            >
              {stats.map((stat, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, scale: 0.8 }}
                  whileInView={{ opacity: 1, scale: 1 }}
                  transition={{ duration: 0.6, delay: stat.delay }}
                  viewport={{ once: true }}
                  className={`floating-card p-6 rounded-xl border-2 ${getColorClasses(stat.color)} 
                             backdrop-blur-sm transition-all duration-300 hover:scale-105`}
                >
                  <div className="text-center">
                    <div className="mb-3 flex justify-center">
                      <div className={`p-3 rounded-lg ${getColorClasses(stat.color)}`}>
                        {stat.icon}
                      </div>
                    </div>
                    <div className="text-3xl font-orbitron font-bold mb-2">
                      {stat.value}{stat.suffix}
                    </div>
                    <div className="text-sm font-poppins opacity-80">
                      {stat.label}
                    </div>
                  </div>
                </motion.div>
              ))}
            </motion.div>
          </div>
        </div>
      </section>

      {/* Values Section */}
      <section className="relative py-20 bg-dark">
        <div className="container mx-auto px-4">
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            viewport={{ once: true }}
            className="text-center mb-16"
          >
            <h2 className="text-4xl md:text-5xl font-audiowide text-white mb-6 neon-text">
              Our Core Values
            </h2>
            <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
              The principles that guide everything we do in our esports community
            </p>
          </motion.div>

          <div className="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
            {[
              {
                title: 'Excellence',
                description: 'We strive for excellence in everything we do, from gameplay to sportsmanship.',
                icon: '🏆',
                color: 'neon-green'
              },
              {
                title: 'Inclusivity',
                description: 'Everyone is welcome regardless of skill level, background, or experience.',
                icon: '🤝',
                color: 'primary-blue'
              },
              {
                title: 'Innovation',
                description: 'We embrace new technologies and strategies to stay ahead of the competition.',
                icon: '💡',
                color: 'cyber-neon-purple'
              }
            ].map((value, index) => (
              <motion.div
                key={index}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.8, delay: index * 0.2 }}
                viewport={{ once: true }}
                className="text-center p-8 rounded-2xl bg-dark-secondary border-2 border-neon-green 
                           hover:border-neon transition-all duration-300 hover:scale-105"
              >
                <div className="text-6xl mb-6">{value.icon}</div>
                <h3 className="text-2xl font-audiowide text-white mb-4">{value.title}</h3>
                <p className="text-gray-300 font-poppins leading-relaxed">{value.description}</p>
              </motion.div>
            ))}
          </div>
        </div>
      </section>

      {/* Call to Action */}
      <section className="relative py-20 bg-dark-secondary">
        <div className="container mx-auto px-4 text-center">
          <motion.div
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.8 }}
            viewport={{ once: true }}
            className="max-w-4xl mx-auto"
          >
            <h2 className="text-4xl md:text-5xl font-audiowide text-white mb-6 neon-text">
              Ready to Join the Community?
            </h2>
            <p className="text-xl text-gray-300 font-poppins mb-8">
              Whether you're a seasoned gamer or just starting out, there's a place for you in DIU Esports.
            </p>
            <div className="flex flex-col sm:flex-row gap-4 justify-center">
              <a
                href="/"
                className="neon-button group inline-flex items-center justify-center"
              >
                <Gamepad2 className="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                Back to Home
              </a>
              <a
                href="#contact"
                className="neon-button group inline-flex items-center justify-center"
              >
                <Users className="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
                Contact Us
              </a>
            </div>
          </motion.div>
        </div>
      </section>
    </main>
  )
}
