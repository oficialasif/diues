'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Trophy, Filter, Grid3X3, List, Calendar, Star, Award, Target, TrendingUp, Medal, Crown, Zap, Flame, Heart, Share2, ExternalLink } from 'lucide-react'
import { apiService, Achievement } from '@/services/api'

const Achievements = () => {
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [selectedYear, setSelectedYear] = useState('all')
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid')
  const [achievements, setAchievements] = useState<Achievement[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  // Fetch achievements from API
  useEffect(() => {
    const fetchAchievements = async () => {
      try {
        setLoading(true)
        const data = await apiService.getAchievements()
        setAchievements(data)
        setError(null)
      } catch (err) {
        console.error('Failed to fetch achievements:', err)
        setError('Failed to load achievements')
      } finally {
        setLoading(false)
      }
    }

    fetchAchievements()
  }, [])

  // Get unique categories and years
  const categories = ['all', ...Array.from(new Set(achievements.map(achievement => achievement.category)))]
  const years = ['all', ...Array.from(new Set(achievements.map(achievement => achievement.year)))]

  // Filter achievements based on selected filters
  const filteredAchievements = achievements.filter(achievement => {
    const categoryMatch = selectedCategory === 'all' || achievement.category === selectedCategory
    const yearMatch = selectedYear === 'all' || achievement.year === selectedYear
    return categoryMatch && yearMatch
  })

  // Get top achievements (using category as a proxy for top achievements)
  const topAchievements = achievements.filter(achievement => achievement.category === 'championship')

  const getCategoryIcon = (category: string) => {
    switch (category.toLowerCase()) {
      case 'championship':
        return '🏆'
      case 'tournament':
        return '🎯'
      case 'league':
        return '⚽'
      case 'individual':
        return '👤'
      case 'team':
        return '👥'
      case 'academic':
        return '📚'
      case 'community':
        return '🤝'
      case 'innovation':
        return '💡'
      default:
        return '🏅'
    }
  }

  const getCategoryColor = (category: string) => {
    switch (category.toLowerCase()) {
      case 'championship':
        return 'text-yellow-400'
      case 'tournament':
        return 'text-blue-400'
      case 'league':
        return 'text-green-400'
      case 'individual':
        return 'text-purple-400'
      case 'team':
        return 'text-pink-400'
      case 'academic':
        return 'text-indigo-400'
      case 'community':
        return 'text-orange-400'
      case 'innovation':
        return 'text-cyan-400'
      default:
        return 'text-gray-400'
    }
  }

  const getAchievementLevel = (category: string) => {
    switch (category.toLowerCase()) {
      case 'championship':
        return { level: 'Legendary', color: 'text-yellow-400', bgColor: 'bg-yellow-400/20', borderColor: 'border-yellow-400' }
      case 'tournament':
        return { level: 'Elite', color: 'text-blue-400', bgColor: 'bg-blue-400/20', borderColor: 'border-blue-400' }
      case 'league':
        return { level: 'Master', color: 'text-green-400', bgColor: 'bg-green-400/20', borderColor: 'border-green-400' }
      case 'individual':
        return { level: 'Expert', color: 'text-purple-400', bgColor: 'bg-purple-400/20', borderColor: 'border-purple-400' }
      case 'team':
        return { level: 'Champion', color: 'text-pink-400', bgColor: 'bg-pink-400/20', borderColor: 'border-pink-400' }
      case 'academic':
        return { level: 'Scholar', color: 'text-indigo-400', bgColor: 'bg-indigo-400/20', borderColor: 'border-indigo-400' }
      case 'community':
        return { level: 'Hero', color: 'text-orange-400', bgColor: 'bg-orange-400/20', borderColor: 'border-orange-400' }
      case 'innovation':
        return { level: 'Pioneer', color: 'text-cyan-400', bgColor: 'bg-cyan-400/20', borderColor: 'border-cyan-400' }
      default:
        return { level: 'Achievement', color: 'text-gray-400', bgColor: 'bg-gray-400/20', borderColor: 'border-gray-400' }
    }
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading achievements...</p>
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
          Achievements
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Celebrating excellence, recognizing talent, and honoring the champions among us
        </p>
        {achievements.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {achievements.length} achievements • {topAchievements.length} championship titles
          </p>
        )}
      </motion.div>

      {/* Top Achievements Section */}
      {topAchievements.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2 }}
          viewport={{ once: true }}
          className="mb-16"
        >
          <div className="text-center mb-12">
            <h3 className="text-3xl font-audiowide text-yellow-400 mb-4 flex items-center justify-center gap-3">
              <Crown className="w-8 h-8" />
              Championship Titles
            </h3>
            <p className="text-gray-400 font-poppins">
              Our most prestigious achievements and championship victories
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            {topAchievements.slice(0, 6).map((achievement, index) => {
              const level = getAchievementLevel(achievement.category)
              return (
                <motion.div
                  key={achievement.id}
                  initial={{ opacity: 0, y: 50 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.6, delay: index * 0.1 }}
                  viewport={{ once: true }}
                  className="group"
                >
                  <div className={`angled-card ${level.bgColor} border-2 ${level.borderColor} rounded-xl p-6 text-center
                                 hover:shadow-neon transition-all duration-300
                                 hover:scale-105`}>
                    
                    {/* Icon */}
                    <div className="mb-4">
                      {achievement.icon_url ? (
                        <img
                          src={achievement.icon_url}
                          alt={achievement.title}
                          className="w-20 h-20 object-contain mx-auto filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                        />
                      ) : (
                        <div className="w-20 h-20 bg-yellow-400/20 rounded-full flex items-center justify-center mx-auto text-4xl">
                          {getCategoryIcon(achievement.category)}
                        </div>
                      )}
                    </div>

                    {/* Title */}
                    <h4 className="text-xl font-russo text-white mb-2">{achievement.title}</h4>
                    
                    {/* Description */}
                    {achievement.description && (
                      <p className="text-gray-300 text-sm font-poppins mb-4 leading-relaxed">
                        {achievement.description}
                      </p>
                    )}

                    {/* Achievement Level */}
                    <div className={`inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-poppins mb-4 ${level.color} border ${level.borderColor}`}>
                      <Crown className="w-4 h-4" />
                      {level.level}
                    </div>

                    {/* Category and Year */}
                    <div className="text-gray-400 text-sm font-poppins">
                      {getCategoryIcon(achievement.category)} {achievement.category} • {achievement.year}
                    </div>

                    {/* Highlights Link */}
                    {achievement.highlights_url && (
                      <a
                        href={achievement.highlights_url}
                        target="_blank"
                        rel="noopener noreferrer"
                        className="inline-flex items-center gap-2 text-yellow-400 hover:text-yellow-300 transition-colors mt-3 text-sm font-poppins"
                      >
                        <ExternalLink className="w-4 h-4" />
                        View Highlights
                      </a>
                    )}
                  </div>
                </motion.div>
              )
            })}
          </div>
        </motion.div>
      )}

      {/* Filters and Controls */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.4 }}
        viewport={{ once: true }}
        className="mb-12"
      >
        <div className="flex flex-col lg:flex-row items-center justify-between gap-6 max-w-6xl mx-auto">
          {/* Category Filter */}
          <div className="flex items-center gap-4">
            <Filter className="w-5 h-5 text-neon-green" />
            <div className="flex flex-wrap gap-2">
              {categories.map((category) => (
                <button
                  key={category}
                  onClick={() => setSelectedCategory(category)}
                  className={`px-4 py-2 rounded-full text-sm font-poppins transition-all duration-300 ${
                    selectedCategory === category
                      ? 'bg-neon-green text-dark border-2 border-neon-green'
                      : 'bg-dark-secondary text-gray-300 border-2 border-gray-600 hover:border-neon-green hover:text-neon-green'
                  }`}
                >
                  {category === 'all' ? 'All Categories' : category}
                </button>
              ))}
            </div>
          </div>

          {/* Year Filter */}
          <div className="flex items-center gap-4">
            <Calendar className="w-5 h-5 text-primary-blue" />
            <select
              value={selectedYear}
              onChange={(e) => setSelectedYear(e.target.value)}
              className="bg-dark-secondary border-2 border-gray-600 rounded-lg px-4 py-2 text-white font-poppins focus:border-neon-green focus:outline-none"
            >
              {years.map((year) => (
                <option key={year} value={year}>
                  {year === 'all' ? 'All Years' : year}
                </option>
              ))}
            </select>
          </div>

          {/* View Mode Toggle */}
          <div className="flex items-center gap-2">
            <button
              onClick={() => setViewMode('grid')}
              className={`p-2 rounded-lg transition-all duration-300 ${
                viewMode === 'grid'
                  ? 'bg-neon-green text-dark'
                  : 'bg-dark-secondary text-gray-400 hover:text-neon-green'
              }`}
            >
              <Grid3X3 className="w-5 h-5" />
            </button>
            <button
              onClick={() => setViewMode('list')}
              className={`p-2 rounded-lg transition-all duration-300 ${
                viewMode === 'list'
                  ? 'bg-neon-green text-dark'
                  : 'bg-dark-secondary text-gray-400 hover:text-neon-green'
              }`}
            >
              <List className="w-5 h-5" />
            </button>
          </div>
        </div>
      </motion.div>

      {/* Achievements Grid/List */}
      {filteredAchievements.length > 0 ? (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          viewport={{ once: true }}
          className="max-w-7xl mx-auto"
        >
          {viewMode === 'grid' ? (
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
              {filteredAchievements.map((achievement, index) => {
                const level = getAchievementLevel(achievement.category)
                return (
                  <motion.div
                    key={achievement.id}
                    initial={{ opacity: 0, y: 50 }}
                    whileInView={{ opacity: 1, y: 0 }}
                    transition={{ duration: 0.6, delay: index * 0.05 }}
                    viewport={{ once: true }}
                    className="group"
                  >
                    <div className="angled-card bg-dark-secondary border-2 border-gray-600 rounded-xl p-4 text-center
                                   hover:border-neon-green hover:shadow-neon transition-all duration-300
                                   hover:scale-105">
                      
                      {/* Icon */}
                      <div className="mb-4">
                        {achievement.icon_url ? (
                          <img
                            src={achievement.icon_url}
                            alt={achievement.title}
                            className="w-16 h-16 object-contain mx-auto filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                          />
                        ) : (
                          <div className="w-16 h-16 bg-gray-600/20 rounded-full flex items-center justify-center mx-auto text-2xl">
                            {getCategoryIcon(achievement.category)}
                          </div>
                        )}
                      </div>

                      {/* Title */}
                      <h4 className="text-lg font-russo text-white mb-2 line-clamp-1">{achievement.title}</h4>
                      
                      {/* Description */}
                      {achievement.description && (
                        <p className="text-gray-300 text-xs font-poppins mb-3 leading-relaxed line-clamp-2">
                          {achievement.description}
                        </p>
                      )}

                      {/* Achievement Level */}
                      <div className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-poppins mb-3 border ${level.color} ${level.borderColor}`}>
                        <Trophy className="w-3 h-3" />
                        {level.level}
                      </div>

                      {/* Category and Year */}
                      <div className="text-gray-400 text-xs font-poppins">
                        {getCategoryIcon(achievement.category)} {achievement.category} • {achievement.year}
                      </div>

                      {/* Highlights Link */}
                      {achievement.highlights_url && (
                        <a
                          href={achievement.highlights_url}
                          target="_blank"
                          rel="noopener noreferrer"
                          className="inline-flex items-center gap-1 text-neon-green hover:text-neon-green/80 transition-colors mt-2 text-xs font-poppins"
                        >
                          <ExternalLink className="w-3 h-3" />
                          Highlights
                        </a>
                      )}
                    </div>
                  </motion.div>
                )
              })}
            </div>
          ) : (
            <div className="space-y-4">
              {filteredAchievements.map((achievement, index) => {
                const level = getAchievementLevel(achievement.category)
                return (
                  <motion.div
                    key={achievement.id}
                    initial={{ opacity: 0, x: -50 }}
                    whileInView={{ opacity: 1, x: 0 }}
                    transition={{ duration: 0.6, delay: index * 0.05 }}
                    viewport={{ once: true }}
                    className="group"
                  >
                    <div className="angled-card bg-dark-secondary border-2 border-gray-600 rounded-xl p-6
                                   hover:border-neon-green hover:shadow-neon transition-all duration-300">
                      <div className="flex items-center gap-6">
                        {/* Icon */}
                        <div className="flex-shrink-0">
                          {achievement.icon_url ? (
                            <img
                              src={achievement.icon_url}
                              alt={achievement.title}
                              className="w-20 h-20 object-contain filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                            />
                          ) : (
                            <div className="w-20 h-20 bg-gray-600/20 rounded-full flex items-center justify-center text-3xl">
                              {getCategoryIcon(achievement.category)}
                            </div>
                          )}
                        </div>

                        {/* Content */}
                        <div className="flex-1 min-w-0">
                          <div className="flex items-start justify-between mb-2">
                            <h4 className="text-xl font-russo text-white">{achievement.title}</h4>
                            <div className={`inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-poppins border ${level.color} ${level.borderColor}`}>
                              <Trophy className="w-4 h-4" />
                              {level.level}
                            </div>
                          </div>
                          
                          {achievement.description && (
                            <p className="text-gray-300 font-poppins leading-relaxed mb-3">
                              {achievement.description}
                            </p>
                          )}

                          <div className="flex items-center gap-4 text-sm text-gray-400">
                            <span className={`font-poppins ${getCategoryColor(achievement.category)}`}>
                              {getCategoryIcon(achievement.category)} {achievement.category}
                            </span>
                            <span className="font-poppins">{achievement.year}</span>
                            {achievement.highlights_url && (
                              <a
                                href={achievement.highlights_url}
                                target="_blank"
                                rel="noopener noreferrer"
                                className="inline-flex items-center gap-1 text-neon-green hover:text-neon-green/80 transition-colors"
                              >
                                <ExternalLink className="w-4 h-4" />
                                View Highlights
                              </a>
                            )}
                          </div>
                        </div>
                      </div>
                    </div>
                  </motion.div>
                )
              })}
            </div>
          )}
        </motion.div>
      ) : (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center py-20"
        >
          <Trophy className="w-16 h-16 text-gray-600 mx-auto mb-4" />
          <p className="text-gray-400 text-xl">No achievements found for the selected filters</p>
          <p className="text-gray-500 mt-2">Try adjusting your category or year selection</p>
        </motion.div>
      )}

      {/* Achievement Stats */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.8 }}
        viewport={{ once: true }}
        className="mt-20 grid grid-cols-1 md:grid-cols-4 gap-6 max-w-4xl mx-auto"
      >
        <div className="text-center">
          <div className="text-3xl font-orbitron text-neon-green mb-2">
            {achievements.length}
          </div>
          <div className="text-gray-400 font-poppins">Total Achievements</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-yellow-400 mb-2">
            {topAchievements.length}
          </div>
          <div className="text-gray-400 font-poppins">Championships</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-primary-blue mb-2">
            {categories.length - 1}
          </div>
          <div className="text-gray-400 font-poppins">Categories</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-purple-400 mb-2">
            {years.length - 1}
          </div>
          <div className="text-gray-400 font-poppins">Years</div>
        </div>
      </motion.div>

      {/* Bottom Decoration */}
      <motion.div
        initial={{ opacity: 0, scaleX: 0 }}
        whileInView={{ opacity: 1, scaleX: 1 }}
        transition={{ duration: 1, delay: 1 }}
        viewport={{ once: true }}
        className="mt-16 flex justify-center"
      >
        <div className="w-32 h-1 bg-gradient-to-r from-transparent via-neon-green to-transparent rounded-full" />
      </motion.div>
    </div>
  )
}

export default Achievements
