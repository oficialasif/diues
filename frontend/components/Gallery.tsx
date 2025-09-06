'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Camera, Filter, Grid3X3, Calendar, Tag, Star, Eye, Heart, Share2, Download, ChevronDown, ChevronUp } from 'lucide-react'
import { apiService, GalleryItem } from '@/services/api'

const Gallery = () => {
  const [selectedCategory, setSelectedCategory] = useState('all')
  const [selectedYear, setSelectedYear] = useState('all')
  const [viewMode, setViewMode] = useState<'grid' | 'masonry'>('grid')
  const [selectedImage, setSelectedImage] = useState<GalleryItem | null>(null)
  const [galleryItems, setGalleryItems] = useState<GalleryItem[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAllImages, setShowAllImages] = useState(false)

  // Fetch gallery items from API
  useEffect(() => {
    const fetchGalleryItems = async () => {
      try {
        setLoading(true)
        const response = await apiService.getGalleryItems()
        
        // Ensure we have an array of items
        if (response && Array.isArray(response)) {
          setGalleryItems(response)

        } else {
          console.warn('Unexpected gallery data structure:', response)
          setGalleryItems([])
        }
        
        setError(null)
      } catch (err) {
        console.error('Failed to fetch gallery items:', err)
        setError('Failed to load gallery items')
        setGalleryItems([])
      } finally {
        setLoading(false)
      }
    }

    fetchGalleryItems()
  }, [])

  // Ensure galleryItems is always an array and has items
  const safeGalleryItems = Array.isArray(galleryItems) ? galleryItems : []
  
  // Get unique categories and years (only if we have items)
  const categories = safeGalleryItems.length > 0 
    ? ['all', ...Array.from(new Set(safeGalleryItems.map(item => item.category).filter(Boolean)))]
    : ['all']
    
  const years = safeGalleryItems.length > 0 
    ? ['all', ...Array.from(new Set(safeGalleryItems.map(item => item.year).filter(Boolean)))]
    : ['all']

  // Filter items based on selected category and year
  const filteredItems = safeGalleryItems.filter(item => {
    const categoryMatch = selectedCategory === 'all' || item.category === selectedCategory
    const yearMatch = selectedYear === 'all' || item.year === selectedYear
    return categoryMatch && yearMatch
  })

  // Get items to display based on showAllImages state
  const displayedItems = showAllImages ? filteredItems : filteredItems.slice(0, 12)

  // Get featured items (using category as a proxy for featured)
  const featuredItems = safeGalleryItems.filter(item => item.category === 'tournament')

  const openImageModal = (item: GalleryItem) => {
    setSelectedImage(item)
  }

  const closeImageModal = () => {
    setSelectedImage(null)
  }

  const toggleShowAll = () => {
    setShowAllImages(!showAllImages)
  }

  const getCategoryIcon = (category: string) => {
    switch (category.toLowerCase()) {
      case 'tournaments':
        return '🏆'
      case 'events':
        return '🎉'
      case 'team':
        return '👥'
      case 'achievement':
        return '🏅'
      case 'community':
        return '🤝'
      case 'gaming':
        return '🎮'
      default:
        return '📸'
    }
  }

  const getCategoryColor = (category: string) => {
    switch (category.toLowerCase()) {
      case 'tournaments':
        return 'text-yellow-400'
      case 'events':
        return 'text-blue-400'
      case 'team':
        return 'text-green-400'
      case 'achievement':
        return 'text-purple-400'
      case 'community':
        return 'text-pink-400'
      case 'gaming':
        return 'text-orange-400'
      default:
        return 'text-gray-400'
    }
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading gallery items...</p>
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

  // Show message if no gallery items
  if (safeGalleryItems.length === 0) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <Camera className="h-16 w-16 text-gray-400 mx-auto mb-4" />
          <p className="text-gray-400 text-xl">No gallery items available</p>
          <p className="text-gray-500 mt-2">Check back later for updates</p>
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
          Gallery & Memories
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Relive our greatest moments, tournaments, and community highlights
        </p>
        {galleryItems.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {galleryItems.length} memories captured and shared
          </p>
        )}
      </motion.div>

      {/* Featured Items Carousel */}
      {featuredItems.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2 }}
          viewport={{ once: true }}
          className="mb-16"
        >
          <div className="text-center mb-12">
            <h3 className="text-3xl font-audiowide text-neon-green mb-4 flex items-center justify-center gap-3">
              <Star className="w-8 h-8" />
              Featured Highlights
            </h3>
            <p className="text-gray-400 font-poppins">
              Our most memorable tournament moments and achievements
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
            {featuredItems.slice(0, 3).map((item, index) => (
              <motion.div
                key={item.id}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                viewport={{ once: true }}
                className="group cursor-pointer"
                onClick={() => openImageModal(item)}
              >
                <div className="angled-card bg-dark-secondary border-2 border-neon-green rounded-xl overflow-hidden
                               hover:border-neon-green hover:shadow-neon transition-all duration-300
                               hover:scale-105">
                  
                  {/* Image */}
                  <div className="relative aspect-video overflow-hidden">
                    {item.image_url ? (
                      <img
                        src={item.image_url}
                        alt={item.title}
                        className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                      />
                    ) : (
                      <div className="w-full h-full bg-dark flex items-center justify-center text-4xl">
                        {getCategoryIcon(item.category)}
                      </div>
                    )}
                    
                    {/* Overlay */}
                    <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center">
                      <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                        <Eye className="w-8 h-8 text-white mx-auto mb-2" />
                        <p className="text-white font-poppins">Click to view</p>
                      </div>
                    </div>
                  </div>

                  {/* Content */}
                  <div className="p-4">
                    <h4 className="text-lg font-russo text-white mb-2 line-clamp-1">{item.title}</h4>
                    {item.description && (
                      <p className="text-gray-300 text-sm font-poppins leading-relaxed mb-4 line-clamp-2">
                        {item.description}
                      </p>
                    )}
                    
                    <div className="flex items-center justify-between text-xs text-gray-400">
                      <span className={`font-poppins ${getCategoryColor(item.category)}`}>
                        {getCategoryIcon(item.category)} {item.category}
                      </span>
                      <span className="font-poppins">{item.year}</span>
                    </div>
                  </div>
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
              onClick={() => setViewMode('masonry')}
              className={`p-2 rounded-lg transition-all duration-300 ${
                viewMode === 'masonry'
                  ? 'bg-neon-green text-dark'
                  : 'bg-dark-secondary text-gray-400 hover:text-neon-green'
              }`}
            >
              <Camera className="w-5 h-5" />
            </button>
          </div>
        </div>
      </motion.div>

      {/* Gallery Grid */}
      {displayedItems.length > 0 ? (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          viewport={{ once: true }}
          className={`grid gap-6 max-w-7xl mx-auto ${
            viewMode === 'grid' 
              ? 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4' 
              : 'columns-1 md:columns-2 lg:columns-3 xl:columns-4'
          }`}
        >
          {displayedItems.map((item, index) => (
            <motion.div
              key={item.id}
              initial={{ opacity: 0, y: 50 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.6, delay: index * 0.05 }}
              viewport={{ once: true }}
              className={`group cursor-pointer ${
                viewMode === 'masonry' ? 'break-inside-avoid mb-6' : ''
              }`}
              onClick={() => openImageModal(item)}
            >
              <div className="angled-card bg-dark-secondary border-2 border-gray-600 rounded-xl overflow-hidden
                             hover:border-neon-green hover:shadow-neon transition-all duration-300
                             hover:scale-105">
                
                {/* Image */}
                <div className="relative aspect-square overflow-hidden">
                  {item.image_url ? (
                    <img
                      src={item.image_url}
                      alt={item.title}
                      className="w-full h-full object-cover transition-transform duration-300 group-hover:scale-110"
                    />
                  ) : (
                    <div className="w-full h-full bg-dark flex items-center justify-center text-4xl">
                      {getCategoryIcon(item.category)}
                    </div>
                  )}
                  
                  {/* Overlay */}
                  <div className="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-50 transition-all duration-300 flex items-center justify-center">
                    <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                      <Eye className="w-6 h-6 text-white mx-auto mb-2" />
                      <p className="text-white font-poppins text-sm">Click to view</p>
                    </div>
                  </div>
                </div>

                {/* Content */}
                <div className="p-4">
                  <h4 className="text-base font-russo text-white mb-2 line-clamp-1">{item.title}</h4>
                  {item.description && (
                    <p className="text-gray-300 text-xs font-poppins leading-relaxed mb-3 line-clamp-2">
                      {item.description}
                    </p>
                  )}
                  
                  <div className="flex items-center justify-between text-xs text-gray-400">
                    <span className={`font-poppins ${getCategoryColor(item.category)}`}>
                      {getCategoryIcon(item.category)} {item.category}
                    </span>
                    <span className="font-poppins">{item.year}</span>
                  </div>

                  {/* Tags */}
                  {item.tags && (
                    <div className="mt-3 flex flex-wrap gap-1">
                      {JSON.parse(item.tags).slice(0, 3).map((tag: string, idx: number) => (
                        <span
                          key={idx}
                          className="px-2 py-1 bg-dark rounded text-xs text-gray-400 font-poppins"
                        >
                          {tag.trim()}
                        </span>
                      ))}
                    </div>
                  )}
                </div>
              </div>
            </motion.div>
          ))}
        </motion.div>
      ) : (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.6 }}
          viewport={{ once: true }}
          className="text-center py-20"
        >
          <Camera className="w-16 h-16 text-gray-600 mx-auto mb-4" />
          <p className="text-gray-400 text-xl">No images found for the selected filters</p>
          <p className="text-gray-500 mt-2">Try adjusting your category or year selection</p>
        </motion.div>
      )}

      {/* Show More/Less Button */}
      {filteredItems.length > 12 && (
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
              {showAllImages ? (
                <>
                  <ChevronUp className="w-5 h-5 group-hover:animate-bounce" />
                  Show Less
                </>
              ) : (
                <>
                  <ChevronDown className="w-5 h-5 group-hover:animate-bounce" />
                  Show More Images
                </>
              )}
            </span>
            <div className="mt-2 text-sm text-gray-400">
              {showAllImages ? 'Collapse to 12 images' : `${filteredItems.length - 12} more images available`}
            </div>
          </motion.button>
        </motion.div>
      )}

      {/* Image Modal */}
      <AnimatePresence>
        {selectedImage && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black bg-opacity-90 flex items-center justify-center z-50 p-4"
            onClick={closeImageModal}
          >
            <motion.div
              initial={{ scale: 0.5, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.5, opacity: 0 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="bg-dark-secondary border-2 border-neon-green rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Modal Header */}
              <div className="flex items-center justify-between p-6 border-b border-gray-600">
                <div>
                  <h3 className="text-2xl font-audiowide text-white">{selectedImage.title}</h3>
                  <p className="text-gray-400 font-poppins">
                    {selectedImage.category} • {selectedImage.year}
                  </p>
                </div>
                <button
                  onClick={closeImageModal}
                  className="text-gray-400 hover:text-white transition-colors text-2xl"
                >
                  ✕
                </button>
              </div>

              {/* Image */}
              <div className="p-6">
                {selectedImage.image_url ? (
                  <img
                    src={selectedImage.image_url}
                    alt={selectedImage.title}
                    className="w-full max-h-96 object-cover rounded-lg mx-auto"
                  />
                ) : (
                  <div className="w-full h-96 bg-dark flex items-center justify-center text-8xl rounded-lg">
                    {getCategoryIcon(selectedImage.category)}
                  </div>
                )}
              </div>

              {/* Description */}
              {selectedImage.description && (
                <div className="px-6 pb-6">
                  <p className="text-gray-300 font-poppins leading-relaxed">
                    {selectedImage.description}
                  </p>
                </div>
              )}

              {/* Tags */}
              {selectedImage.tags && (
                <div className="px-6 pb-6">
                  <h4 className="text-lg font-russo text-neon-green mb-3 flex items-center gap-2">
                    <Tag className="w-5 h-5" />
                    Tags
                  </h4>
                  <div className="flex flex-wrap gap-2">
                    {JSON.parse(selectedImage.tags).map((tag: string, idx: number) => (
                      <span
                        key={idx}
                        className="px-3 py-1 bg-dark rounded-full text-sm text-gray-300 font-poppins border border-gray-600"
                      >
                        {tag.trim()}
                      </span>
                    ))}
                  </div>
                </div>
              )}

              {/* Action Buttons */}
              <div className="px-6 pb-6 flex gap-4">
                <button className="neon-button flex-1">
                  <span className="flex items-center justify-center gap-2">
                    <Heart className="w-5 h-5" />
                    Like
                  </span>
                </button>
                <button className="neon-button flex-1">
                  <span className="flex items-center justify-center gap-2">
                    <Share2 className="w-5 h-5" />
                    Share
                  </span>
                </button>
                <button className="neon-button flex-1">
                  <span className="flex items-center justify-center gap-2">
                    <Download className="w-5 h-5" />
                    Download
                  </span>
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Gallery Stats */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.8 }}
        viewport={{ once: true }}
        className="mt-20 grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-4 md:gap-6 max-w-4xl mx-auto"
      >
        <div className="text-center">
          <div className="text-3xl font-orbitron text-neon-green mb-2">
            {galleryItems.length}
          </div>
          <div className="text-gray-400 font-poppins">Total Images</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-primary-blue mb-2">
            {featuredItems.length}
          </div>
          <div className="text-gray-400 font-poppins">Tournament</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-yellow-400 mb-2">
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

export default Gallery
