'use client'

import { useEffect, useRef } from 'react'
import { motion } from 'framer-motion'
import CountUp from 'react-countup'
import { Trophy, Users, Gamepad2, Calendar, Award, Target, Zap, Star } from 'lucide-react'

const QuickStats = () => {
  const containerRef = useRef<HTMLDivElement>(null)

  useEffect(() => {
    // GSAP animations for floating cards
    const initFloatingAnimation = async () => {
      const { gsap } = await import('gsap')
      
      if (containerRef.current) {
        const cards = containerRef.current.querySelectorAll('.floating-card')
        
        cards.forEach((card, index) => {
          gsap.to(card, {
            y: -20,
            duration: 3 + index * 0.5,
            ease: 'power2.inOut',
            yoyo: true,
            repeat: -1,
            delay: index * 0.3,
          })
        })
      }
    }

    initFloatingAnimation()
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
      icon: <Award className="w-8 h-8" />,
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
    <div className="container mx-auto px-4">
      {/* Section Header */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8 }}
        viewport={{ once: true }}
        className="text-center mb-16"
      >
        <h2 className="text-4xl md:text-6xl font-audiowide text-white mb-6 neon-text">
          Our Achievements
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Numbers that define our esports excellence and community growth
        </p>
      </motion.div>

      {/* Stats Grid */}
      <div
        ref={containerRef}
        className="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 md:gap-6 max-w-7xl mx-auto"
      >
        {stats.map((stat, index) => (
          <motion.div
            key={index}
            initial={{ opacity: 0, scale: 0.5 }}
            whileInView={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.6, delay: stat.delay }}
            viewport={{ once: true }}
            className="floating-card group"
          >
            <div className={`
              relative p-6 rounded-2xl border-2 bg-opacity-20 backdrop-blur-sm
              transition-all duration-300 hover:scale-105 hover:shadow-neon
              ${getColorClasses(stat.color)}
            `}>
              {/* Holographic effect */}
              <div className="absolute inset-0 rounded-2xl bg-gradient-to-br from-white/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300" />
              
              {/* Icon */}
              <div className="relative z-10 text-center mb-4">
                <div className="inline-block p-3 rounded-full bg-black bg-opacity-30 backdrop-blur-sm">
                  {stat.icon}
                </div>
              </div>

              {/* Counter */}
              <div className="relative z-10 text-center mb-2">
                <div className="text-3xl md:text-4xl font-orbitron font-bold">
                  <CountUp
                    end={stat.value}
                    duration={2.5}
                    delay={stat.delay}
                    separator=","
                    decimals={stat.label === 'Rating' ? 1 : 0}
                  />
                  {stat.suffix}
                </div>
              </div>

              {/* Label */}
              <div className="relative z-10 text-center">
                <p className="text-sm font-poppins font-medium uppercase tracking-wider">
                  {stat.label}
                </p>
              </div>

              {/* Glowing border effect */}
              <div className="absolute inset-0 rounded-2xl border-2 border-transparent group-hover:border-current transition-all duration-300 opacity-0 group-hover:opacity-100" />
            </div>
          </motion.div>
        ))}
      </div>

      {/* Bottom Decoration */}
      <motion.div
        initial={{ opacity: 0, scaleX: 0 }}
        whileInView={{ opacity: 1, scaleX: 1 }}
        transition={{ duration: 1, delay: 0.5 }}
        viewport={{ once: true }}
        className="mt-16 flex justify-center"
      >
        <div className="w-32 h-1 bg-gradient-to-r from-transparent via-neon-green to-transparent rounded-full" />
      </motion.div>
    </div>
  )
}

export default QuickStats
