'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Building2, Filter, Grid3X3, List, Calendar, Star, Award, Users, TrendingUp, Globe, Shield, Heart, Share2, ExternalLink, ChevronDown, ChevronUp } from 'lucide-react'
import { apiService, Sponsor } from '@/services/api'

const Sponsors = () => {
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [selectedPartnershipType, setSelectedPartnershipType] = useState('all')
  const [viewMode, setViewMode] = useState<'grid' | 'list'>('grid')
  const [sponsors, setSponsors] = useState<Sponsor[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAllSponsors, setShowAllSponsors] = useState(false)

  // Fetch sponsors from API
  useEffect(() => {
    const fetchSponsors = async () => {
      try {
        setLoading(true)
        const response = await apiService.getSponsors()
        
        // Ensure we have an array of sponsors
        if (response && Array.isArray(response)) {
          setSponsors(response)

        } else {
          console.warn('Unexpected sponsors data structure:', response)
          setSponsors([])
        }
        
        setError(null)
      } catch (err) {
        console.error('Failed to fetch sponsors:', err)
        setError('Failed to load sponsors')
        setSponsors([])
      } finally {
        setLoading(false)
      }
    }

    fetchSponsors()
  }, [])

  // Ensure sponsors is always an array
  const safeSponsors = Array.isArray(sponsors) ? sponsors : []

  // Get unique categories and partnership types
  const categories = safeSponsors.length > 0 
    ? ['all', ...Array.from(new Set(safeSponsors.map(sponsor => sponsor.category).filter(Boolean)))]
    : ['all']
    
  const partnershipTypes = safeSponsors.length > 0 
    ? ['all', ...Array.from(new Set(safeSponsors.map(sponsor => sponsor.partnership_type).filter(Boolean)))]
    : ['all']

  // Filter sponsors based on selected filters
  const filteredSponsors = safeSponsors.filter(sponsor => {
    const categoryMatch = selectedCategory === 'all' || sponsor.category === selectedCategory
    const partnershipMatch = selectedPartnershipType === 'all' || sponsor.partnership_type === selectedPartnershipType
    return categoryMatch && partnershipMatch
  })

  // Get sponsors to display based on showAllSponsors state
  const displayedSponsors = showAllSponsors ? filteredSponsors : filteredSponsors.slice(0, 6)

  // Get premium sponsors (using partnership_type as a proxy for premium)
  const premiumSponsors = safeSponsors.filter(sponsor => sponsor.partnership_type === 'platinum')

  const getCategoryIcon = (category: string) => {
    switch (category.toLowerCase()) {
      case 'gaming':
        return '🎮'
      case 'technology':
        return '💻'
      case 'sports':
        return '⚽'
      case 'entertainment':
        return '🎬'
      case 'education':
        return '📚'
      case 'finance':
        return '💰'
      case 'healthcare':
        return '🏥'
      case 'automotive':
        return '🚗'
      default:
        return '🏢'
    }
  }

  const getCategoryColor = (category: string) => {
    switch (category.toLowerCase()) {
      case 'gaming':
        return 'text-green-400'
      case 'technology':
        return 'text-blue-400'
      case 'sports':
        return 'text-yellow-400'
      case 'entertainment':
        return 'text-pink-400'
      case 'education':
        return 'text-purple-400'
      case 'finance':
        return 'text-emerald-400'
      case 'healthcare':
        return 'text-red-400'
      case 'automotive':
        return 'text-orange-400'
      default:
        return 'text-gray-400'
    }
  }

  const getPartnershipColor = (type: string) => {
    switch (type.toLowerCase()) {
      case 'platinum':
        return 'text-yellow-400 border-yellow-400'
      case 'gold':
        return 'text-yellow-500 border-yellow-500'
      case 'silver':
        return 'text-gray-400 border-gray-400'
      case 'bronze':
        return 'text-orange-600 border-orange-600'
      default:
        return 'text-gray-400 border-gray-400'
    }
  }

  const getPartnershipIcon = (type: string) => {
    switch (type.toLowerCase()) {
      case 'platinum':
        return '💎'
      case 'gold':
        return '🥇'
      case 'silver':
        return '🥈'
      case 'bronze':
        return '🥉'
      default:
        return '🤝'
    }
  }

  const toggleShowAll = () => {
    setShowAllSponsors(!showAllSponsors)
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading sponsors...</p>
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
          Sponsors & Partners
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Our valued partners who support our mission and enable esports excellence
        </p>
        {sponsors.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {sponsors.length} partners supporting our community
          </p>
        )}
      </motion.div>

      {/* Premium Sponsors */}
      {premiumSponsors.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2 }}
          viewport={{ once: true }}
          className="mb-16"
        >
          <div className="text-center mb-12">
            <h3 className="text-3xl font-audiowide text-yellow-400 mb-4 flex items-center justify-center gap-3">
              <Star className="w-8 h-8" />
              Platinum Partners
            </h3>
            <p className="text-gray-400 font-poppins">
              Our top-tier sponsors who make everything possible
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
            {premiumSponsors.map((sponsor, index) => (
              <motion.div
                key={sponsor.id}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                viewport={{ once: true }}
                className="group"
              >
                <div className="angled-card bg-gradient-to-br from-yellow-400/20 to-yellow-600/20 border-2 border-yellow-400 rounded-xl p-6 text-center
                               hover:border-yellow-300 hover:shadow-yellow transition-all duration-300
                               hover:scale-105">
                  
                  {/* Logo */}
                  <div className="mb-4">
                    {sponsor.logo_url ? (
                      <img
                        src={sponsor.logo_url}
                        alt={sponsor.name}
                        className="w-20 h-20 object-contain mx-auto filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                      />
                    ) : (
                      <div className="w-20 h-20 bg-yellow-400/20 rounded-full flex items-center justify-center mx-auto text-3xl">
                        {getCategoryIcon(sponsor.category)}
                      </div>
                    )}
                  </div>

                  {/* Name */}
                  <h4 className="text-xl font-russo text-white mb-2">{sponsor.name}</h4>
                  
                  {/* Description */}
                  {sponsor.description && (
                    <p className="text-gray-300 text-sm font-poppins mb-4 leading-relaxed">
                      {sponsor.description}
                    </p>
                  )}

                  {/* Partnership Type */}
                  <div className="inline-flex items-center gap-2 px-3 py-1 bg-yellow-400/20 rounded-full text-yellow-400 border border-yellow-400 text-sm font-poppins mb-4">
                    <Star className="w-4 h-4" />
                    Platinum Partner
                  </div>

                  {/* Category */}
                  <div className="text-gray-400 text-sm font-poppins">
                    {getCategoryIcon(sponsor.category)} {sponsor.category}
                  </div>

                  {/* Website Link */}
                  {sponsor.website_url && (
                    <a
                      href={sponsor.website_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-2 text-yellow-400 hover:text-yellow-300 transition-colors mt-3 text-sm font-poppins"
                    >
                      <ExternalLink className="w-4 h-4" />
                      Visit Website
                    </a>
                  )}
                </div>
              </motion.div>
            ))}
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

          {/* Partnership Type Filter */}
          <div className="flex items-center gap-4">
            <Award className="w-5 h-5 text-primary-blue" />
            <select
              value={selectedPartnershipType}
              onChange={(e) => setSelectedPartnershipType(e.target.value)}
              className="bg-dark-secondary border-2 border-gray-600 rounded-lg px-4 py-2 text-white font-poppins focus:border-neon-green focus:outline-none"
            >
              {partnershipTypes.map((type) => (
                <option key={type} value={type}>
                  {type === 'all' ? 'All Types' : type}
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

      {/* Sponsors Grid/List */}
      {displayedSponsors.length > 0 ? (
        viewMode === 'grid' ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-7xl mx-auto">
            {displayedSponsors.map((sponsor, index) => (
              <motion.div
                key={sponsor.id}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.05 }}
                viewport={{ once: true }}
                className="group"
              >
                <div className="angled-card bg-dark-secondary border-2 border-gray-600 rounded-xl p-4 text-center
                               hover:border-neon-green hover:shadow-neon transition-all duration-300
                               hover:scale-105">
                  
                  {/* Logo */}
                  <div className="mb-4">
                    {sponsor.logo_url ? (
                      <img
                        src={sponsor.logo_url}
                        alt={sponsor.name}
                        className="w-16 h-16 object-contain mx-auto filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                      />
                    ) : (
                      <div className="w-16 h-16 bg-gray-600/20 rounded-full flex items-center justify-center mx-auto text-2xl">
                        {getCategoryIcon(sponsor.category)}
                      </div>
                    )}
                  </div>

                  {/* Name */}
                  <h4 className="text-lg font-russo text-white mb-2 line-clamp-1">{sponsor.name}</h4>
                  
                  {/* Description */}
                  {sponsor.description && (
                    <p className="text-gray-300 text-xs font-poppins mb-3 leading-relaxed line-clamp-2">
                      {sponsor.description}
                    </p>
                  )}

                  {/* Partnership Type */}
                  <div className={`inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-poppins mb-3 border ${getPartnershipColor(sponsor.partnership_type)}`}>
                    {getPartnershipIcon(sponsor.partnership_type)}
                    {sponsor.partnership_type}
                  </div>

                  {/* Category */}
                  <div className="text-gray-400 text-xs font-poppins">
                    {getCategoryIcon(sponsor.category)} {sponsor.category}
                  </div>

                  {/* Website Link */}
                  {sponsor.website_url && (
                    <a
                      href={sponsor.website_url}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="inline-flex items-center gap-1 text-neon-green hover:text-neon-green/80 transition-colors mt-2 text-xs font-poppins"
                    >
                      <ExternalLink className="w-3 h-3" />
                      Website
                    </a>
                  )}
                </div>
              </motion.div>
            ))}
          </div>
        ) : (
          <div className="space-y-4">
            {displayedSponsors.map((sponsor, index) => (
              <motion.div
                key={sponsor.id}
                initial={{ opacity: 0, x: -50 }}
                whileInView={{ opacity: 1, x: 0 }}
                transition={{ duration: 0.6, delay: index * 0.05 }}
                viewport={{ once: true }}
                className="group"
              >
                <div className="angled-card bg-dark-secondary border-2 border-gray-600 rounded-xl p-6
                               hover:border-neon-green hover:shadow-neon transition-all duration-300">
                  <div className="flex items-center gap-6">
                    {/* Logo */}
                    <div className="flex-shrink-0">
                      {sponsor.logo_url ? (
                        <img
                          src={sponsor.logo_url}
                          alt={sponsor.name}
                          className="w-20 h-20 object-contain filter brightness-0 invert group-hover:brightness-100 group-hover:invert-0 transition-all duration-300"
                        />
                      ) : (
                        <div className="w-20 h-20 bg-gray-600/20 rounded-full flex items-center justify-center text-3xl">
                          {getCategoryIcon(sponsor.category)}
                        </div>
                      )}
                    </div>

                    {/* Content */}
                    <div className="flex-1 min-w-0">
                      <div className="flex items-start justify-between mb-2">
                        <h4 className="text-xl font-russo text-white">{sponsor.name}</h4>
                        <div className={`inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-poppins border ${getPartnershipColor(sponsor.partnership_type)}`}>
                          {getPartnershipIcon(sponsor.partnership_type)}
                          {sponsor.partnership_type}
                        </div>
                      </div>
                      
                      {sponsor.description && (
                        <p className="text-gray-300 font-poppins leading-relaxed mb-3">
                          {sponsor.description}
                        </p>
                      )}

                      <div className="flex items-center gap-4 text-sm text-gray-400">
                        <span className={`font-poppins ${getCategoryColor(sponsor.category)}`}>
                          {getCategoryIcon(sponsor.category)} {sponsor.category}
                        </span>
                        {sponsor.website_url && (
                          <a
                            href={sponsor.website_url}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="inline-flex items-center gap-1 text-neon-green hover:text-neon-green/80 transition-colors"
                          >
                            <ExternalLink className="w-4 h-4" />
                            Website
                          </a>
                        )}
                      </div>
                    </div>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>
        )
      ) : (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center py-20"
        >
          <Building2 className="w-16 h-16 text-gray-600 mx-auto mb-4" />
          <p className="text-gray-400 text-xl">No sponsors found for the selected filters</p>
          <p className="text-gray-500 mt-2">Try adjusting your category or partnership type selection</p>
        </motion.div>
      )}

      {/* Show More/Less Button */}
      {filteredSponsors.length > 6 && (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.3 }}
          viewport={{ once: true }}
          className="flex justify-center mt-12"
        >
          <motion.button
            onClick={toggleShowAll}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className="floating-show-more-btn group"
          >
            <span className="flex items-center gap-3 text-lg font-poppins">
              {showAllSponsors ? (
                <>
                  <ChevronUp className="w-5 h-5 group-hover:animate-bounce" />
                  Show Less
                </>
              ) : (
                <>
                  <ChevronDown className="w-5 h-5 group-hover:animate-bounce" />
                  Show More Sponsors
                </>
              )}
            </span>
            <div className="mt-2 text-sm text-gray-400">
              {showAllSponsors ? 'Collapse to 6 sponsors' : `${filteredSponsors.length - 6} more sponsors available`}
            </div>
          </motion.button>
        </motion.div>
      )}

      {/* Sponsor Stats */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.8 }}
        viewport={{ once: true }}
        className="mt-20 grid grid-cols-1 md:grid-cols-4 gap-6 max-w-4xl mx-auto"
      >
        <div className="text-center">
          <div className="text-3xl font-orbitron text-neon-green mb-2">
            {sponsors.length}
          </div>
          <div className="text-gray-400 font-poppins">Total Partners</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-yellow-400 mb-2">
            {premiumSponsors.length}
          </div>
          <div className="text-gray-400 font-poppins">Platinum</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-primary-blue mb-2">
            {categories.length - 1}
          </div>
          <div className="text-gray-400 font-poppins">Categories</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-purple-400 mb-2">
            {partnershipTypes.length - 1}
          </div>
          <div className="text-gray-400 font-poppins">Partnership Types</div>
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

export default Sponsors
