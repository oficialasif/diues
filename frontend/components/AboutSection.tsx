'use client'

import { motion } from 'framer-motion'
import { Target, Users, Zap, Trophy, Shield, Heart } from 'lucide-react'

const AboutSection = () => {
  const features = [
    {
      icon: <Target className="w-6 h-6" />,
      title: 'Competitive Excellence',
      description: 'Fostering top-tier gaming talent through structured tournaments and training programs.',
      color: 'neon-green',
    },
    {
      icon: <Users className="w-6 h-6" />,
      title: 'Community First',
      description: 'Building a supportive network where every gamer feels valued and empowered.',
      color: 'primary-blue',
    },
    {
      icon: <Zap className="w-6 h-6" />,
      title: 'Innovation Hub',
      description: 'Pioneering new approaches to esports education and competitive gaming.',
      color: 'cyber-neon-purple',
    },
    {
      icon: <Trophy className="w-6 h-6" />,
      title: 'Championship Mindset',
      description: 'Instilling the drive for excellence and continuous improvement in all members.',
      color: 'cyber-neon-pink',
    },
  ]

  const getColorClasses = (color: string) => {
    const colorMap: { [key: string]: string } = {
      'neon-green': 'text-neon-green border-neon-green',
      'primary-blue': 'text-primary-blue border-primary-blue',
      'cyber-neon-purple': 'text-cyber-neon-purple border-cyber-neon-purple',
      'cyber-neon-pink': 'text-cyber-neon-pink border-cyber-neon-pink',
    }
    return colorMap[color] || colorMap['neon-green']
  }

  return (
    <div className="container mx-auto px-4">
      <div className="grid lg:grid-cols-2 gap-16 items-center">
        {/* Left Side - Mission & Vision */}
        <motion.div
          initial={{ opacity: 0, x: -50 }}
          whileInView={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.8 }}
          viewport={{ once: true }}
          className="space-y-8"
        >
          {/* Section Header */}
          <div>
            <h2 className="text-4xl md:text-6xl font-audiowide text-white mb-6 neon-text">
              About DIU ESPORTS
            </h2>
            <div className="w-20 h-1 bg-neon-green mb-6" />
          </div>

          {/* Mission Statement */}
          <div className="space-y-4">
            <h3 className="text-2xl font-russo text-neon-green">Our Mission</h3>
            <p className="text-lg text-gray-300 font-poppins leading-relaxed">
              To create an inclusive, competitive, and innovative esports ecosystem that empowers 
              students to excel in gaming while developing essential life skills, leadership qualities, 
              and a strong sense of community.
            </p>
          </div>

          {/* Vision Statement */}
          <div className="space-y-4">
            <h3 className="text-2xl font-russo text-primary-blue">Our Vision</h3>
            <p className="text-lg text-gray-300 font-poppins leading-relaxed">
              To be the leading university esports community in the region, recognized for excellence 
              in competitive gaming, community building, and professional development, while setting 
              new standards for esports education and innovation.
            </p>
          </div>

          {/* Core Values */}
          <div className="space-y-4">
            <h3 className="text-2xl font-russo text-cyber-neon-purple">Core Values</h3>
            <div className="grid grid-cols-1 gap-4">
              {features.map((feature, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.6, delay: index * 0.1 }}
                  viewport={{ once: true }}
                  className={`p-4 rounded-lg border-l-4 bg-dark-secondary bg-opacity-50 backdrop-blur-sm ${getColorClasses(feature.color)}`}
                >
                  <div className="flex items-start gap-3">
                    <div className="mt-1">
                      {feature.icon}
                    </div>
                    <div>
                      <h4 className="font-russo text-lg mb-2">{feature.title}</h4>
                      <p className="text-gray-300 text-sm font-poppins leading-relaxed">
                        {feature.description}
                      </p>
                    </div>
                  </div>
                </motion.div>
              ))}
            </div>
          </div>

          {/* CTA Button */}
          <motion.div
            initial={{ opacity: 0, y: 20 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: 0.5 }}
            viewport={{ once: true }}
            className="pt-4"
          >
            <button className="neon-button group">
              <span className="flex items-center gap-2">
                <Heart className="w-5 h-5 group-hover:animate-pulse" />
                Join Our Community
              </span>
            </button>
          </motion.div>
        </motion.div>

        {/* Right Side - Cyberpunk Gaming Illustration */}
        <motion.div
          initial={{ opacity: 0, x: 50 }}
          whileInView={{ opacity: 1, x: 0 }}
          transition={{ duration: 0.8 }}
          viewport={{ once: true }}
          className="relative"
        >
          {/* Main Illustration Container */}
          <div className="relative w-full h-96 lg:h-[500px] bg-gradient-to-br from-dark-secondary via-primary-blue-dark to-dark rounded-2xl overflow-hidden">
            {/* Animated Grid Background */}
            <div className="absolute inset-0 cyber-grid opacity-30" />
            
            {/* Glowing Circuit Lines */}
            <div className="absolute inset-0">
              <svg className="w-full h-full" viewBox="0 0 400 500">
                {/* Circuit paths */}
                <path
                  d="M50,100 Q200,50 350,100 L350,200 Q200,250 50,200 Z"
                  stroke="#22C55E"
                  strokeWidth="2"
                  fill="none"
                  className="animate-pulse"
                  opacity="0.6"
                />
                <path
                  d="M50,300 Q200,250 350,300 L350,400 Q200,450 50,400 Z"
                  stroke="#1D4ED8"
                  strokeWidth="2"
                  fill="none"
                  className="animate-pulse"
                  opacity="0.6"
                />
                {/* Connection nodes */}
                <circle cx="200" cy="150" r="4" fill="#22C55E" className="animate-pulse" />
                <circle cx="200" cy="350" r="4" fill="#1D4ED8" className="animate-pulse" />
                <circle cx="200" cy="250" r="6" fill="#8B5CF6" className="animate-pulse" />
              </svg>
            </div>

            {/* Floating Gaming Elements */}
            <div className="absolute top-10 left-10 w-16 h-16 bg-neon-green rounded-lg opacity-80 animate-float">
              <div className="w-full h-full bg-gradient-to-br from-neon-green to-green-600 rounded-lg flex items-center justify-center">
                <span className="text-2xl">🎮</span>
              </div>
            </div>

            <div className="absolute top-20 right-16 w-12 h-12 bg-primary-blue rounded-full opacity-80 animate-float" style={{ animationDelay: '1s' }}>
              <div className="w-full h-full bg-gradient-to-br from-primary-blue to-blue-600 rounded-full flex items-center justify-center">
                <span className="text-xl">🏆</span>
              </div>
            </div>

            <div className="absolute bottom-20 left-20 w-14 h-14 bg-cyber-neon-purple rounded-lg opacity-80 animate-float" style={{ animationDelay: '2s' }}>
              <div className="w-full h-full bg-gradient-to-br from-cyber-neon-purple to-purple-600 rounded-lg flex items-center justify-center">
                <span className="text-xl">⚡</span>
              </div>
            </div>

            <div className="absolute bottom-32 right-10 w-10 h-10 bg-cyber-neon-pink rounded-full opacity-80 animate-float" style={{ animationDelay: '3s' }}>
              <div className="w-full h-full bg-gradient-to-br from-cyber-neon-pink to-pink-600 rounded-full flex items-center justify-center">
                <span className="text-lg">💎</span>
              </div>
            </div>

            {/* Central Holographic Display */}
            <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
              <div className="relative">
                {/* Rotating outer ring */}
                <div className="w-32 h-32 border-2 border-neon-green rounded-full animate-rotate-slow opacity-60" />
                
                {/* Inner content */}
                <div className="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-24 h-24 bg-dark rounded-full border-2 border-primary-blue flex items-center justify-center shadow-neon-blue">
                  <div className="text-center">
                    <div className="text-2xl mb-1">🎯</div>
                    <div className="text-xs font-orbitron text-neon-green uppercase tracking-wider">
                      DIU
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {/* Floating particles */}
            {[...Array(8)].map((_, i) => (
              <div
                key={i}
                className="absolute w-2 h-2 bg-neon-green rounded-full animate-pulse"
                style={{
                  left: `${20 + (i * 10)}%`,
                  top: `${30 + (i * 8)}%`,
                  animationDelay: `${i * 0.5}s`,
                }}
              />
            ))}
          </div>

          {/* Bottom Decoration */}
          <div className="absolute -bottom-4 left-1/2 transform -translate-x-1/2 w-32 h-1 bg-gradient-to-r from-transparent via-neon-green to-transparent rounded-full" />
        </motion.div>
      </div>
    </div>
  )
}

export default AboutSection
