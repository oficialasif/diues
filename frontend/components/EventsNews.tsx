'use client'

import { useState, useEffect } from 'react'
import { motion } from 'framer-motion'
import { Calendar, Clock, MapPin, Users, Trophy, Star, ArrowRight, ArrowLeft } from 'lucide-react'
import { apiService, Event, CountdownSettings } from '@/services/api'

const EventsNews = () => {
  const [currentIndex, setCurrentIndex] = useState(0)
  const [timeLeft, setTimeLeft] = useState('')
  const [events, setEvents] = useState<Event[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [countdownSettings, setCountdownSettings] = useState<CountdownSettings | null>(null)

  // Fetch events from API
  useEffect(() => {
    const fetchEvents = async () => {
      try {
        setLoading(true)
        const response = await apiService.getEvents()
        
        // Ensure we have an array of events
        if (response && Array.isArray(response)) {
          setEvents(response)

        } else {
          console.warn('Unexpected events data structure:', response)
          setEvents([])
        }
        setError(null)
      } catch (err) {
        console.error('Failed to fetch events:', err)
        setError('Failed to load events')
        setEvents([])
      } finally {
        setLoading(false)
      }
    }

    fetchEvents()
  }, [])

  // Fetch countdown settings from API with real-time updates
  const fetchCountdownSettings = async () => {
    try {
      const data = await apiService.getCountdownSettings()
      
      // Check if data exists and has required fields
      if (data && typeof data === 'object' && 'target_date' in data && 'status_text' in data) {
        setCountdownSettings(data)
      } else {
        throw new Error('Invalid data structure')
      }
    } catch (err) {
      console.error('Failed to fetch countdown settings:', err)
      // Don't use default settings - let the countdown be hidden
      setCountdownSettings(null)
    }
  }

  useEffect(() => {
    // Initial fetch
    fetchCountdownSettings()

    // Set up real-time updates every 30 seconds
    const interval = setInterval(fetchCountdownSettings, 30000)

    // More frequent updates when page is visible (every 10 seconds)
    let fastInterval: NodeJS.Timeout | null = null
    
    const handleVisibilityChange = () => {
      if (document.hidden) {
        // Page is hidden, use slower updates
        if (fastInterval) {
          clearInterval(fastInterval)
          fastInterval = null
        }
      } else {
        // Page is visible, use faster updates
        fastInterval = setInterval(fetchCountdownSettings, 10000)
      }
    }

    // Listen for page visibility changes
    document.addEventListener('visibilitychange', handleVisibilityChange)
    
    // Initial visibility check
    if (!document.hidden) {
      fastInterval = setInterval(fetchCountdownSettings, 10000)
    }

    // Cleanup intervals and event listener on component unmount
    return () => {
      clearInterval(interval)
      if (fastInterval) {
        clearInterval(fastInterval)
      }
      document.removeEventListener('visibilitychange', handleVisibilityChange)
    }
  }, [])

  // Ensure events is always an array
  const safeEvents = Array.isArray(events) ? events : []

  // Calculate time left based on admin countdown settings
  useEffect(() => {
    if (!countdownSettings || !countdownSettings.show_countdown) {
      setTimeLeft('')
      return
    }

    const timer = setInterval(() => {
      const targetTime = new Date(countdownSettings.target_date).getTime()
      const now = new Date().getTime()
      const difference = targetTime - now

      if (difference > 0) {
        const days = Math.floor(difference / (1000 * 60 * 60 * 24))
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60))
        const minutes = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60))
        const seconds = Math.floor((difference % (1000 * 60)) / 1000)

        let newTimeLeft = ''
        switch (countdownSettings.countdown_type) {
          case 'days':
            if (days > 0) {
              newTimeLeft = `${days}d ${hours}h ${minutes}m ${seconds}s`
            } else if (hours > 0) {
              newTimeLeft = `${hours}h ${minutes}m ${seconds}s`
            } else if (minutes > 0) {
              newTimeLeft = `${minutes}m ${seconds}s`
            } else {
              newTimeLeft = `${seconds}s`
            }
            break
          case 'hours':
            if (hours > 0) {
              newTimeLeft = `${hours}h ${minutes}m ${seconds}s`
            } else if (minutes > 0) {
              newTimeLeft = `${minutes}m ${seconds}s`
            } else {
              newTimeLeft = `${seconds}s`
            }
            break
          case 'minutes':
            if (minutes > 0) {
              newTimeLeft = `${minutes}m ${seconds}s`
            } else {
              newTimeLeft = `${seconds}s`
            }
            break
          case 'seconds':
            newTimeLeft = `${seconds}s`
            break
          default:
            newTimeLeft = `${days}d ${hours}h ${minutes}m ${seconds}s`
        }
        
        setTimeLeft(newTimeLeft)
      } else {
        setTimeLeft(countdownSettings.status_text)
      }
    }, 1000)

    return () => clearInterval(timer)
  }, [countdownSettings])

  const nextSlide = () => {
    setCurrentIndex((prevIndex) => (prevIndex + 1) % safeEvents.length)
  }

  const prevSlide = () => {
    setCurrentIndex((prevIndex) => (prevIndex + safeEvents.length - 1) % safeEvents.length)
  }

  const getStatusColor = (status: string) => {
    switch (status) {
      case 'upcoming':
        return 'text-blue-400'
      case 'ongoing':
        return 'text-green-400'
      case 'completed':
        return 'text-gray-400'
      case 'cancelled':
        return 'text-red-400'
      default:
        return 'text-gray-400'
    }
  }

  const getStatusIcon = (status: string) => {
    switch (status) {
      case 'upcoming':
        return '⏳'
      case 'ongoing':
        return '🔥'
      case 'completed':
        return '✅'
      case 'cancelled':
        return '❌'
      default:
        return '📅'
    }
  }

  const getTypeIcon = (type: string) => {
    switch (type) {
      case 'tournament':
        return '🏆'
      case 'workshop':
        return '📚'
      case 'meetup':
        return '🤝'
      case 'celebration':
        return '🎉'
      default:
        return '🎮'
    }
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading events...</p>
        </div>
      </div>
    )
  }

  if (error) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <p className="text-red-400 text-xl">{error}</p>
          <p className="text-gray-400 mt-2">Please try refreshing the page</p>
        </div>
      </div>
    )
  }

  if (safeEvents.length === 0) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <p className="text-gray-400 text-xl">No events available at the moment</p>
          <p className="text-gray-500 mt-2">Check back later for upcoming events</p>
        </div>
      </div>
    )
  }

  const currentEvent = safeEvents[currentIndex]

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
          Events & News
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Stay updated with our latest events, tournaments, and community activities
        </p>
        {safeEvents.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {safeEvents.length} events scheduled
          </p>
        )}
      </motion.div>

      {/* Countdown Timer */}
      {timeLeft && countdownSettings && (
        <motion.div
          initial={{ opacity: 0, scale: 0.8 }}
          whileInView={{ opacity: 1, scale: 1 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center mb-12"
        >
          <div className="inline-block bg-dark-secondary border-2 border-neon-green rounded-2xl px-8 py-4 relative">
            <p className="text-gray-300 font-poppins mb-2">Next Event Starting In</p>
            <p className="text-3xl font-orbitron text-neon-green">{timeLeft}</p>
            {countdownSettings.custom_message && (
              <p className="text-sm text-gray-400 font-poppins mt-2 max-w-md mx-auto">
                {countdownSettings.custom_message}
              </p>
            )}
          </div>
        </motion.div>
      )}

      {/* Featured Event Carousel */}
      <div className="max-w-6xl mx-auto">
        <div className="relative">
          {/* Navigation Arrows */}
          <button
            onClick={prevSlide}
            className="absolute left-4 top-1/2 transform -translate-y-1/2 z-10 
                     bg-dark-secondary border-2 border-neon-green rounded-full p-3
                     hover:bg-neon-green hover:text-dark transition-all duration-300
                     text-white"
          >
            <ArrowLeft className="w-6 h-6" />
          </button>

          <button
            onClick={nextSlide}
            className="absolute right-4 top-1/2 transform -translate-y-1/2 z-10 
                     bg-dark-secondary border-2 border-neon-green rounded-full p-3
                     hover:bg-neon-green hover:text-dark transition-all duration-300
                     text-white"
          >
            <ArrowRight className="w-6 h-6" />
          </button>

          {/* Event Card */}
          <motion.div
            key={currentEvent.id}
            initial={{ opacity: 0, x: 100 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.5 }}
            className="angled-card bg-dark-secondary border-2 border-neon-green rounded-2xl p-8"
          >
            <div className="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
              {/* Event Image */}
              <div className="text-center lg:text-left">
                <div className="text-8xl mb-4">{getTypeIcon(currentEvent.event_type)}</div>
                {currentEvent.poster_url && (
                  <img
                    src={currentEvent.poster_url}
                    alt={currentEvent.title}
                    className="w-full max-w-md mx-auto lg:mx-0 rounded-lg shadow-lg"
                  />
                )}
              </div>

              {/* Event Details */}
              <div className="space-y-6">
                {/* Event Header */}
                <div>
                  <div className="flex items-center gap-3 mb-3">
                    <span className={`text-sm font-poppins uppercase tracking-wider ${getStatusColor(currentEvent.status)}`}>
                      {getStatusIcon(currentEvent.status)} {currentEvent.status}
                    </span>
                  </div>
                  <h3 className="text-3xl md:text-4xl font-audiowide text-white mb-2">
                    {currentEvent.title}
                  </h3>
                  <p className="text-gray-300 font-poppins leading-relaxed">
                    {currentEvent.description}
                  </p>
                </div>

                {/* Event Info Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div className="flex items-center gap-3 text-gray-300">
                    <Calendar className="w-5 h-5 text-neon-green" />
                    <span className="font-poppins">{new Date(currentEvent.event_date).toLocaleDateString()}</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-300">
                    <Clock className="w-5 h-5 text-primary-blue" />
                    <span className="font-poppins">{new Date(currentEvent.event_date).toLocaleTimeString()}</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-300">
                    <MapPin className="w-5 h-5 text-yellow-400" />
                    <span className="font-poppins">{currentEvent.location}</span>
                  </div>
                  <div className="flex items-center gap-3 text-gray-300">
                    <Users className="w-5 h-5 text-purple-400" />
                    <span className="font-poppins">Open to All</span>
                  </div>
                </div>

                {/* Action Buttons */}
                <div className="flex flex-col sm:flex-row gap-4">
                  <button className="neon-button flex-1">
                    <span className="flex items-center justify-center gap-2">
                      <Trophy className="w-5 h-5" />
                      Register Now
                    </span>
                  </button>
                  <button className="neon-button-outline flex-1">
                    <span className="flex items-center justify-center gap-2">
                      <Calendar className="w-5 h-5" />
                      Add to Calendar
                    </span>
                  </button>
                </div>
              </div>
            </div>
          </motion.div>

          {/* Event Indicators */}
          <div className="flex justify-center mt-8 space-x-2">
            {safeEvents.map((_, index) => (
              <button
                key={index}
                onClick={() => setCurrentIndex(index)}
                className={`w-3 h-3 rounded-full transition-all duration-300 ${
                  index === currentIndex
                    ? 'bg-neon-green scale-125'
                    : 'bg-gray-600 hover:bg-gray-400'
                }`}
              />
            ))}
          </div>
        </div>
      </div>

      {/* Quick Stats */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.3 }}
        viewport={{ once: true }}
        className="mt-20 grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 md:gap-6 max-w-4xl mx-auto"
      >
        <div className="text-center">
          <div className="text-3xl font-orbitron text-neon-green mb-2">
            {safeEvents.filter(e => e.status === 'upcoming').length}
          </div>
          <div className="text-gray-400 font-poppins">Upcoming</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-primary-blue mb-2">
            {safeEvents.filter(e => e.status === 'ongoing').length}
          </div>
          <div className="text-gray-400 font-poppins">Ongoing</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-yellow-400 mb-2">
            {safeEvents.filter(e => e.event_type === 'workshop').length}
          </div>
          <div className="text-gray-400 font-poppins">Workshops</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-purple-400 mb-2">
            {safeEvents.filter(e => e.event_type === 'tournament').length}
          </div>
          <div className="text-gray-400 font-poppins">Tournaments</div>
        </div>
      </motion.div>

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

export default EventsNews
