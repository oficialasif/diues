'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Crown, Users, Award, Star, ChevronDown, ChevronUp, Shield, Zap } from 'lucide-react'
import { apiService, CommitteeMember } from '@/services/api'

const Leadership = () => {
  const [showPastCommittees, setShowPastCommittees] = useState(false)
  const [showAllCurrent, setShowAllCurrent] = useState(false)
  const [committeeMembers, setCommitteeMembers] = useState<CommitteeMember[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  // Fetch committee members from API
  useEffect(() => {
    const fetchCommitteeMembers = async () => {
      try {
        setLoading(true)
        const response = await apiService.getCommitteeMembers()
        
        // Ensure we have an array of committee members
        if (response && Array.isArray(response)) {
          setCommitteeMembers(response)

        } else {
          console.warn('Unexpected committee members data structure:', response)
          setCommitteeMembers([])
        }
        
        setError(null)
      } catch (err) {
        console.error('Failed to fetch committee members:', err)
        setError('Failed to load committee members')
        setCommitteeMembers([])
      } finally {
        setLoading(false)
      }
    }

    fetchCommitteeMembers()
  }, [])

  // Ensure committeeMembers is always an array
  const safeCommitteeMembers = Array.isArray(committeeMembers) ? committeeMembers : []

  // Separate current and past committee members
  const currentCommittee = safeCommitteeMembers.filter(member => member.is_current)
  const pastCommittees = safeCommitteeMembers.filter(member => !member.is_current)

  // Get current committee members to display based on showAllCurrent state
  const displayedCurrentCommittee = showAllCurrent ? currentCommittee : currentCommittee.slice(0, 3)

  // Group past committees by year
  const pastCommitteesByYear = pastCommittees.reduce((acc, member) => {
    const year = member.year
    if (!acc[year]) {
      acc[year] = []
    }
    acc[year].push(member)
    return acc
  }, {} as Record<string, CommitteeMember[]>)

  const getRoleIcon = (role: string) => {
    switch (role.toLowerCase()) {
      case 'president':
        return '👑'
      case 'vice president':
        return '⚡'
      case 'general secretary':
        return '📋'
      case 'treasurer':
        return '💰'
      case 'event manager':
        return '🎯'
      case 'marketing head':
        return '📢'
      default:
        return '👤'
    }
  }

  const getRoleColor = (role: string) => {
    switch (role.toLowerCase()) {
      case 'president':
        return 'text-yellow-400'
      case 'vice president':
        return 'text-blue-400'
      case 'general secretary':
        return 'text-green-400'
      case 'treasurer':
        return 'text-purple-400'
      case 'event manager':
        return 'text-orange-400'
      case 'marketing head':
        return 'text-pink-400'
      default:
        return 'text-gray-400'
    }
  }

  const toggleShowAllCurrent = () => {
    setShowAllCurrent(!showAllCurrent)
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading committee members...</p>
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
          Leadership Committee
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Meet our dedicated team of leaders who drive innovation and excellence in esports
        </p>
        {committeeMembers.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {currentCommittee.length} current members • {pastCommittees.length} past members
          </p>
        )}
      </motion.div>

      {/* Current Committee */}
      {currentCommittee.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.2 }}
          viewport={{ once: true }}
          className="mb-16"
        >
          <div className="text-center mb-12">
            <h3 className="text-3xl font-audiowide text-neon-green mb-4 flex items-center justify-center gap-3">
              <Crown className="w-8 h-8" />
              Current Committee {new Date().getFullYear()}
            </h3>
            <p className="text-gray-400 font-poppins">
              Leading the charge towards esports excellence
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            {displayedCurrentCommittee.map((member, index) => (
              <motion.div
                key={member.id}
                initial={{ opacity: 0, y: 50 }}
                whileInView={{ opacity: 1, y: 0 }}
                transition={{ duration: 0.6, delay: index * 0.1 }}
                viewport={{ once: true }}
                className="group"
              >
                <div className="angled-card bg-dark-secondary border-2 border-neon-green rounded-xl p-6 
                               hover:border-neon-green hover:shadow-neon transition-all duration-300
                               hover:scale-105 hover:rotate-0 text-center">
                  
                  {/* Member Image */}
                  <div className="mb-6">
                    {member.image_url ? (
                      <img
                        src={member.image_url}
                        alt={member.name}
                        className="w-24 h-24 rounded-full mx-auto border-4 border-neon-green object-cover"
                      />
                    ) : (
                      <div className="w-24 h-24 rounded-full mx-auto border-4 border-neon-green bg-dark flex items-center justify-center text-4xl">
                        {getRoleIcon(member.role)}
                      </div>
                    )}
                  </div>

                  {/* Member Info */}
                  <div className="mb-4">
                    <h4 className="text-xl font-russo text-white mb-2">{member.name}</h4>
                    <p className={`text-lg font-poppins ${getRoleColor(member.role)} mb-1`}>
                      {member.role}
                    </p>
                    <p className="text-sm text-gray-400 font-poppins">
                      {member.position}
                    </p>
                  </div>

                  {/* Bio */}
                  {member.bio && (
                    <p className="text-gray-300 text-sm font-poppins leading-relaxed mb-4">
                      {member.bio}
                    </p>
                  )}

                  {/* Achievements */}
                  {member.achievements && (
                    <div className="mb-4">
                      <h5 className="text-sm font-russo text-neon-green mb-2 flex items-center justify-center gap-2">
                        <Award className="w-4 h-4" />
                        Achievements
                      </h5>
                      <div className="text-xs text-gray-400 font-poppins">
                        {member.achievements}
                      </div>
                    </div>
                  )}

                  {/* Social Links */}
                  {member.social_links && (
                    <div className="flex justify-center space-x-3">
                      {JSON.parse(member.social_links)?.discord && (
                        <a href={`https://discord.com/users/${JSON.parse(member.social_links).discord}`} 
                           className="text-gray-400 hover:text-neon-green transition-colors">
                          Discord
                        </a>
                      )}
                      {JSON.parse(member.social_links)?.twitter && (
                        <a href={`https://twitter.com/${JSON.parse(member.social_links).twitter}`} 
                           className="text-gray-400 hover:text-neon-green transition-colors">
                          Twitter
                        </a>
                      )}
                      {JSON.parse(member.social_links)?.linkedin && (
                        <a href={`https://linkedin.com/in/${JSON.parse(member.social_links).linkedin}`} 
                           className="text-gray-400 hover:text-neon-green transition-colors">
                          LinkedIn
                        </a>
                      )}
                    </div>
                  )}

                  {/* Status Badge */}
                  <div className="mt-4">
                    <span className="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-neon-green bg-opacity-20 text-neon-green border border-neon-green">
                      <Shield className="w-3 h-3 mr-1" />
                      Active
                    </span>
                  </div>
                </div>
              </motion.div>
            ))}
          </div>

          {/* Show More/Less Button for Current Committee */}
          {currentCommittee.length > 3 && (
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.3 }}
              viewport={{ once: true }}
              className="flex justify-center mt-12"
            >
              <motion.button
                onClick={toggleShowAllCurrent}
                whileHover={{ scale: 1.05 }}
                whileTap={{ scale: 0.95 }}
                className="floating-show-more-btn group"
              >
                <span className="flex items-center gap-3 text-lg font-poppins">
                  {showAllCurrent ? (
                    <>
                      <ChevronUp className="w-5 h-5 group-hover:animate-bounce" />
                      Show Less
                    </>
                  ) : (
                    <>
                      <ChevronDown className="w-5 h-5 group-hover:animate-bounce" />
                      Show More Members
                    </>
                  )}
                </span>
                <div className="mt-2 text-sm text-gray-400">
                  {showAllCurrent ? 'Collapse to 3 members' : `${currentCommittee.length - 3} more members available`}
                </div>
              </motion.button>
            </motion.div>
          )}
        </motion.div>
      )}

      {/* Past Committees */}
      {pastCommittees.length > 0 && (
        <motion.div
          initial={{ opacity: 0, y: 50 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.4 }}
          viewport={{ once: true }}
        >
          <div className="text-center mb-8">
            <button
              onClick={() => setShowPastCommittees(!showPastCommittees)}
              className="neon-button-outline inline-flex items-center gap-2"
            >
              <span>{showPastCommittees ? 'Hide' : 'Show'} Past Committees</span>
              {showPastCommittees ? <ChevronUp className="w-5 h-5" /> : <ChevronDown className="w-5 h-5" />}
            </button>
          </div>

          <AnimatePresence>
            {showPastCommittees && (
              <motion.div
                initial={{ opacity: 0, height: 0 }}
                animate={{ opacity: 1, height: 'auto' }}
                exit={{ opacity: 0, height: 0 }}
                transition={{ duration: 0.5 }}
                className="overflow-hidden"
              >
                <div className="space-y-12">
                  {Object.entries(pastCommitteesByYear)
                    .sort(([a], [b]) => parseInt(b) - parseInt(a))
                    .map(([year, members]) => (
                      <div key={year} className="text-center">
                        <h4 className="text-2xl font-audiowide text-primary-blue mb-6 flex items-center justify-center gap-3">
                          <Star className="w-6 h-6" />
                          {year} Committee
                        </h4>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-6xl mx-auto">
                          {members.map((member, index) => (
                            <motion.div
                              key={member.id}
                              initial={{ opacity: 0, y: 30 }}
                              whileInView={{ opacity: 1, y: 0 }}
                              transition={{ duration: 0.5, delay: index * 0.1 }}
                              viewport={{ once: true }}
                              className="group"
                            >
                              <div className="angled-card bg-dark border-2 border-gray-600 rounded-lg p-4 
                                             hover:border-gray-500 transition-all duration-300
                                             hover:scale-105 text-center">
                                
                                {/* Member Image */}
                                <div className="mb-4">
                                  {member.image_url ? (
                                    <img
                                      src={member.image_url}
                                      alt={member.name}
                                      className="w-16 h-16 rounded-full mx-auto border-2 border-gray-600 object-cover"
                                    />
                                  ) : (
                                    <div className="w-16 h-16 rounded-full mx-auto border-2 border-gray-600 bg-dark-secondary flex items-center justify-center text-2xl">
                                      {getRoleIcon(member.role)}
                                    </div>
                                  )}
                                </div>

                                {/* Member Info */}
                                <div className="mb-3">
                                  <h5 className="text-lg font-russo text-white mb-1">{member.name}</h5>
                                  <p className={`text-sm font-poppins ${getRoleColor(member.role)}`}>
                                    {member.role}
                                  </p>
                                </div>

                                {/* Legacy Badge */}
                                <div className="mt-3">
                                  <span className="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-600 bg-opacity-20 text-gray-400 border border-gray-600">
                                    <Zap className="w-3 h-3 mr-1" />
                                    Legacy
                                  </span>
                                </div>
                              </div>
                            </motion.div>
                          ))}
                        </div>
                      </div>
                    ))}
                </div>
              </motion.div>
            )}
          </AnimatePresence>
        </motion.div>
      )}

      {/* Committee Stats */}
      <motion.div
        initial={{ opacity: 0, y: 50 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.6 }}
        viewport={{ once: true }}
        className="mt-20 grid grid-cols-1 md:grid-cols-4 gap-6 max-w-4xl mx-auto"
      >
        <div className="text-center">
          <div className="text-3xl font-orbitron text-neon-green mb-2">
            {currentCommittee.length}
          </div>
          <div className="text-gray-400 font-poppins">Current Members</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-primary-blue mb-2">
            {pastCommittees.length}
          </div>
          <div className="text-gray-400 font-poppins">Past Members</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-yellow-400 mb-2">
            {committeeMembers.length > 0 ? Math.max(...committeeMembers.map(m => parseInt(m.year))) : 0}
          </div>
          <div className="text-gray-400 font-poppins">Years Active</div>
        </div>
        <div className="text-center">
          <div className="text-3xl font-orbitron text-purple-400 mb-2">
            {committeeMembers.length}
          </div>
          <div className="text-gray-400 font-poppins">Total Members</div>
        </div>
      </motion.div>

      {/* Bottom Decoration */}
      <motion.div
        initial={{ opacity: 0, scaleX: 0 }}
        whileInView={{ opacity: 1, scaleX: 1 }}
        transition={{ duration: 1, delay: 0.8 }}
        viewport={{ once: true }}
        className="mt-16 flex justify-center"
      >
        <div className="w-32 h-1 bg-gradient-to-r from-transparent via-neon-green to-transparent rounded-full" />
      </motion.div>
    </div>
  )
}

export default Leadership
