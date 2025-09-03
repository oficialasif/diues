'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Gamepad2, Trophy, Users, Calendar, ExternalLink, Play, Award, Filter, X, UserPlus, Mail, Phone, Hash, GraduationCap, Building } from 'lucide-react'
import { apiService, Tournament, TournamentRegistrationForm, TournamentTeamMember } from '@/services/api'

const GamesPortfolio = () => {
  const [selectedGame, setSelectedGame] = useState<string>('all')
  const [tournaments, setTournaments] = useState<Tournament[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAllTournaments, setShowAllTournaments] = useState(false)
  const [selectedTournament, setSelectedTournament] = useState<Tournament | null>(null)
  const [showRegistrationForm, setShowRegistrationForm] = useState(false)
  const [registrationForm, setRegistrationForm] = useState<TournamentRegistrationForm>({
    tournament_id: 0,
    team_name: '',
    team_type: 'solo',
    captain_name: '',
    captain_email: '',
    captain_phone: '',
    captain_discord: '',
    captain_student_id: '',
    captain_department: '',
    captain_semester: '',
    captain_game_username: '',
    team_members: []
  })
  const [registrationLoading, setRegistrationLoading] = useState(false)
  const [registrationSuccess, setRegistrationSuccess] = useState(false)

  // Fetch tournaments from API
  useEffect(() => {
    const fetchTournaments = async () => {
      try {
        setLoading(true)
        const response = await apiService.getTournaments()
        
        // Ensure we have an array of tournaments
        if (response && Array.isArray(response)) {
          setTournaments(response)

        } else {
          console.warn('Unexpected tournaments data structure:', response)
          setTournaments([])
        }
        
        setError(null)
      } catch (err) {
        console.error('Failed to fetch tournaments:', err)
        setError('Failed to load tournaments')
        setTournaments([])
      } finally {
        setLoading(false)
      }
    }

    fetchTournaments()
  }, [])

  // Reset show all when game selection changes
  useEffect(() => {
    setShowAllTournaments(false)
  }, [selectedGame])

  // Open tournament details
  const openTournamentDetails = (tournament: Tournament) => {
    setSelectedTournament(tournament)
  }

  // Close tournament details
  const closeTournamentDetails = () => {
    setSelectedTournament(null)
  }

  // Open registration form
  const openRegistrationForm = (tournament: Tournament) => {
    setRegistrationForm({
      tournament_id: tournament.id,
      team_name: '',
      team_type: 'solo',
      captain_name: '',
      captain_email: '',
      captain_phone: '',
      captain_discord: '',
      captain_student_id: '',
      captain_department: '',
      captain_semester: '',
      captain_game_username: '',
      team_members: []
    })
    setShowRegistrationForm(true)
    setSelectedTournament(null)
  }

  // Close registration form
  const closeRegistrationForm = () => {
    setShowRegistrationForm(false)
    setRegistrationForm({
      tournament_id: 0,
      team_name: '',
      team_type: 'solo',
      captain_name: '',
      captain_email: '',
      captain_phone: '',
      captain_discord: '',
      captain_student_id: '',
      captain_department: '',
      captain_semester: '',
      captain_game_username: '',
      team_members: []
    })
    setRegistrationSuccess(false)
  }

  // Handle form input changes
  const handleFormChange = (field: keyof TournamentRegistrationForm, value: any) => {
    setRegistrationForm(prev => ({ ...prev, [field]: value }))
  }

  // Add team member
  const addTeamMember = () => {
    const newMember: TournamentTeamMember = {
      id: Date.now(), // Temporary ID
      registration_id: 0,
      player_name: '',
      player_email: '',
      player_phone: '',
      player_discord: '',
      player_student_id: '',
      player_department: '',
      player_semester: '',
      player_role: 'member',
      game_username: '',
      created_at: new Date().toISOString()
    }
    setRegistrationForm(prev => ({
      ...prev,
      team_members: [...(prev.team_members || []), newMember]
    }))
  }

  // Remove team member
  const removeTeamMember = (index: number) => {
    setRegistrationForm(prev => ({
      ...prev,
      team_members: (prev.team_members || []).filter((_, i) => i !== index)
    }))
  }

  // Handle team member form changes
  const handleTeamMemberChange = (index: number, field: keyof TournamentTeamMember, value: any) => {
    setRegistrationForm(prev => ({
      ...prev,
      team_members: (prev.team_members || []).map((member, i) => 
        i === index ? { ...member, [field]: value } : member
      )
    }))
  }

  // Submit registration
  const submitRegistration = async () => {
    try {
      setRegistrationLoading(true)
      await apiService.registerForTournament(registrationForm)
      setRegistrationSuccess(true)
      setTimeout(() => {
        closeRegistrationForm()
      }, 3000)
    } catch (err) {
      console.error('Registration failed:', err)
      const errorMessage = err instanceof Error ? err.message : 'Unknown error occurred'
      alert(`Registration failed: ${errorMessage}`)
    } finally {
      setRegistrationLoading(false)
    }
  }

  // Ensure tournaments is always an array
  const safeTournaments = Array.isArray(tournaments) ? tournaments : []

  // Get unique games from tournaments
  const getUniqueGames = () => {
    if (safeTournaments.length === 0) return []
    
    const games = safeTournaments
      .filter(t => t.game_name)
      .map(t => ({ name: t.game_name, genre: t.genre }))
      .filter((game, index, self) => 
        index === self.findIndex(g => g.name === game.name)
      )
    return games
  }

  // Filter tournaments by selected game
  const getFilteredTournaments = () => {
    let filtered = []
    
    if (selectedGame === 'all') {
      filtered = safeTournaments
    } else {
      filtered = safeTournaments.filter(t => t.game_name === selectedGame)
    }
    
    // Limit to 3 tournaments initially unless showAllTournaments is true
    if (!showAllTournaments && filtered.length > 3) {
      return filtered.slice(0, 3)
    }
    
    return filtered
  }

  // Get total count of filtered tournaments (before limiting)
  const getTotalFilteredCount = () => {
    if (selectedGame === 'all') {
      return safeTournaments.length
    }
    return safeTournaments.filter(t => t.game_name === selectedGame).length
  }

  const uniqueGames = getUniqueGames()
  const filteredTournaments = getFilteredTournaments()

  return (
    <div className="relative py-20">
      {/* Section Header */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8 }}
        viewport={{ once: true }}
        className="text-center mb-16"
      >
        <h2 className="text-5xl md:text-6xl font-orbitron font-bold text-white mb-6 neon-text">
          Games & Tournaments
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Explore our competitive gaming tournaments across different titles. 
          Join the battle and prove your skills in our organized competitions.
        </p>
      </motion.div>

      {/* Game Filter Tabs */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.2 }}
        viewport={{ once: true }}
        className="mb-12"
      >
        <div className="flex flex-wrap justify-center gap-4 max-w-6xl mx-auto">
          {/* All Games Tab */}
          <button
            onClick={() => setSelectedGame('all')}
            className={`px-6 py-3 rounded-lg font-poppins font-medium transition-all duration-300 ${
              selectedGame === 'all'
                ? 'bg-neon-green text-dark border-2 border-neon-green shadow-neon'
                : 'bg-dark-secondary text-gray-300 border-2 border-gray-600 hover:border-neon-green hover:text-neon-green'
            }`}
          >
            <Filter className="w-4 h-4 inline mr-2" />
            All Games
          </button>

          {/* Individual Game Tabs */}
          {uniqueGames.map((game, index) => (
            <motion.button
              key={game.name}
              initial={{ opacity: 0, scale: 0.8 }}
              whileInView={{ opacity: 1, scale: 1 }}
              transition={{ duration: 0.5, delay: 0.1 * index }}
              viewport={{ once: true }}
              onClick={() => setSelectedGame(game.name)}
              className={`px-6 py-3 rounded-lg font-poppins font-medium transition-all duration-300 ${
                selectedGame === game.name
                  ? 'bg-neon-green text-dark border-2 border-neon-green shadow-neon'
                  : 'bg-dark-secondary text-gray-300 border-2 border-gray-600 hover:border-neon-green hover:text-neon-green'
              }`}
            >
              <Gamepad2 className="w-4 h-4 inline mr-2" />
              {game.name}
              <span className="ml-2 text-xs opacity-75">({game.genre})</span>
            </motion.button>
          ))}
        </div>
      </motion.div>

      {/* Tournaments Display */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.4 }}
        viewport={{ once: true }}
        className="max-w-7xl mx-auto"
      >
        {loading ? (
          <div className="text-center py-20">
            <div className="animate-spin rounded-full h-16 w-16 border-b-2 border-neon-green mx-auto mb-4"></div>
            <p className="text-gray-400">Loading tournaments...</p>
          </div>
        ) : error ? (
          <div className="text-center py-20">
            <p className="text-red-400 text-lg">{error}</p>
          </div>
        ) : filteredTournaments.length === 0 ? (
          <div className="text-center py-20">
            <Trophy className="w-16 h-16 text-gray-600 mx-auto mb-4" />
            <p className="text-gray-400 text-lg">
              {selectedGame === 'all' 
                ? 'No tournaments found. Check back later for upcoming competitions!'
                : `No tournaments found for ${selectedGame}. Try selecting a different game.`
              }
            </p>
          </div>
        ) : (
          <>
            {/* Tournaments Count */}
            <div className="text-center mb-8">
              <p className="text-gray-400">
                Showing {filteredTournaments.length} tournament{filteredTournaments.length !== 1 ? 's' : ''}
                {selectedGame !== 'all' && ` for ${selectedGame}`}
              </p>
            </div>

            {/* Tournaments Grid */}
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              {filteredTournaments.map((tournament, index) => (
                <motion.div
                  key={tournament.id}
                  initial={{ opacity: 0, y: 50 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.6, delay: index * 0.1 }}
                  viewport={{ once: true }}
                  className="group"
                >
                  <div className="bg-dark-secondary border-2 border-gray-700 rounded-xl p-6 
                                 hover:border-neon-green hover:shadow-neon transition-all duration-300
                                 hover:scale-105">
                    
                    {/* Tournament Header */}
                    <div className="mb-4">
                      <div className="flex items-center justify-between mb-2">
                        <span className="text-xs text-neon-green font-medium uppercase tracking-wider">
                          {tournament.game_name}
                        </span>
                        <span className={`px-2 py-1 text-xs rounded-full font-medium ${
                          tournament.status === 'upcoming' ? 'bg-blue-900/30 text-blue-400 border border-blue-500' :
                          tournament.status === 'ongoing' ? 'bg-green-900/30 text-green-400 border border-green-500' :
                          tournament.status === 'completed' ? 'bg-gray-900/30 text-gray-400 border border-gray-500' :
                          'bg-red-900/30 text-red-400 border border-red-500'
                        }`}>
                          {tournament.status}
                        </span>
                      </div>
                      <h3 className="text-xl font-russo text-white mb-2 line-clamp-2">
                        {tournament.name}
                      </h3>
                      <p className="text-sm text-gray-400 line-clamp-2">
                        {tournament.description}
                      </p>
                    </div>

                    {/* Tournament Details */}
                    <div className="space-y-3 mb-6">
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-gray-400 flex items-center gap-2">
                          <Calendar className="w-4 h-4" />
                          Start Date
                        </span>
                        <span className="text-white font-medium">
                          {new Date(tournament.start_date).toLocaleDateString()}
                        </span>
                      </div>
                      
                      <div className="flex items-center justify-between text-sm">
                        <span className="text-gray-400 flex items-center gap-2">
                          <Users className="w-4 h-4" />
                          Participants
                        </span>
                        <span className="text-white font-medium">
                          {tournament.current_participants || 0} / {tournament.max_participants || '∞'}
                        </span>
                      </div>

                      {tournament.prize_pool && (
                        <div className="flex items-center justify-between text-sm">
                          <span className="text-gray-400 flex items-center gap-2">
                            <Trophy className="w-4 h-4" />
                            Prize Pool
                          </span>
                          <span className="text-yellow-400 font-medium">
                            ${tournament.prize_pool.toLocaleString()}
                          </span>
                        </div>
                      )}
                    </div>

                    {/* Action Button */}
                    <button 
                      onClick={() => openTournamentDetails(tournament)}
                      className="w-full neon-button group"
                    >
                      <span className="flex items-center justify-center gap-2">
                        <Play className="w-4 h-4 group-hover:scale-110 transition-transform" />
                        View Details
                      </span>
                    </button>
                  </div>
                </motion.div>
              ))}
            </div>
          </>
        )}
      </motion.div>

      {/* Show More/Less Button */}
      {getTotalFilteredCount() > 3 && (
        <motion.div
          initial={{ opacity: 0, y: 30 }}
          whileInView={{ opacity: 1, y: 0 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          viewport={{ once: true }}
          className="flex justify-center mt-12"
        >
          <motion.button
            onClick={() => setShowAllTournaments(!showAllTournaments)}
            whileHover={{ scale: 1.05 }}
            whileTap={{ scale: 0.95 }}
            className="neon-button group px-8 py-4 text-lg font-poppins"
          >
            <span className="flex items-center gap-3">
              {showAllTournaments ? (
                <>
                  <i className="fas fa-chevron-up group-hover:animate-bounce" />
                  Show Less
                </>
              ) : (
                <>
                  <i className="fas fa-chevron-down group-hover:animate-bounce" />
                  Show More Tournaments
                </>
              )}
            </span>
            <div className="mt-2 text-sm text-gray-400">
              {showAllTournaments 
                ? 'Collapse to 3 tournaments' 
                : `${getTotalFilteredCount() - 3} more tournaments available`
              }
            </div>
          </motion.button>
        </motion.div>
      )}

      {/* Tournament Details Modal */}
      <AnimatePresence>
        {selectedTournament && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4"
            onClick={closeTournamentDetails}
          >
            <motion.div
              initial={{ scale: 0.5, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.5, opacity: 0 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="bg-dark-secondary border-2 border-neon-green rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* SHOWING DETAILS Banner */}
              <div className="bg-gradient-to-r from-neon-green to-blue-500 text-dark font-bold text-center py-3 px-6 rounded-lg mb-6">
                <span className="text-lg">SHOWING DETAILS</span>
              </div>

              {/* Modal Header */}
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-4">
                  <div className="text-4xl">🎮</div>
                  <div>
                    <h3 className="text-3xl font-audiowide text-white">{selectedTournament.name}</h3>
                    <p className="text-neon-green font-poppins uppercase tracking-wider">
                      {selectedTournament.game_name} • {selectedTournament.genre}
                    </p>
                  </div>
                </div>
                <button
                  onClick={closeTournamentDetails}
                  className="text-gray-400 hover:text-white transition-colors text-2xl"
                >
                  <X className="w-6 h-6" />
                </button>
              </div>

              {/* Tournament Description */}
              <div className="mb-6">
                <p className="text-gray-300 font-poppins leading-relaxed text-lg">
                  {selectedTournament.description}
                </p>
              </div>

              {/* Tournament Stats Grid */}
              <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
                <div className="bg-dark rounded-lg p-4 text-center border border-neon-green">
                  <div className="text-2xl font-orbitron text-neon-green">
                    {new Date(selectedTournament.start_date).toLocaleDateString()}
                  </div>
                  <div className="text-sm text-gray-400">Start Date</div>
                </div>
                <div className="bg-dark rounded-lg p-4 text-center border border-blue-500">
                  <div className="text-2xl font-orbitron text-blue-400">
                    {new Date(selectedTournament.end_date).toLocaleDateString()}
                  </div>
                  <div className="text-sm text-gray-400">End Date</div>
                </div>
                <div className="bg-dark rounded-lg p-4 text-center border border-purple-500">
                  <div className="text-2xl font-orbitron text-purple-400">
                    {selectedTournament.current_participants || 0} / {selectedTournament.max_participants || '∞'}
                  </div>
                  <div className="text-sm text-gray-400">Participants</div>
                </div>
                {selectedTournament.prize_pool && (
                  <div className="bg-dark rounded-lg p-4 text-center border border-yellow-500">
                    <div className="text-2xl font-orbitron text-yellow-400">
                      ${selectedTournament.prize_pool.toLocaleString()}
                    </div>
                    <div className="text-sm text-gray-400">Prize Pool</div>
                  </div>
                )}
              </div>

              {/* Tournament Status */}
              <div className="mb-8 text-center">
                <span className={`px-4 py-2 rounded-full text-sm font-medium ${
                  selectedTournament.status === 'upcoming' ? 'bg-blue-900/30 text-blue-400 border border-blue-500' :
                  selectedTournament.status === 'ongoing' ? 'bg-green-900/30 text-green-400 border border-green-500' :
                  selectedTournament.status === 'completed' ? 'bg-gray-900/30 text-gray-400 border border-gray-500' :
                  'bg-red-900/30 text-red-400 border border-red-500'
                }`}>
                  Status: {selectedTournament.status.charAt(0).toUpperCase() + selectedTournament.status.slice(1)}
                </span>
              </div>

              {/* Action Buttons */}
              <div className="flex flex-col sm:flex-row gap-4">
                <button 
                  onClick={() => openRegistrationForm(selectedTournament)}
                  className="neon-button flex-1 py-4 text-lg font-poppins"
                >
                  <span className="flex items-center justify-center gap-3">
                    <Trophy className="w-6 h-6" />
                    Register Now
                  </span>
                </button>
                <button className="bg-gray-600 hover:bg-gray-700 text-white font-semibold py-4 px-6 rounded-lg transition-all duration-300 flex-1">
                  <span className="flex items-center justify-center gap-3">
                    <ExternalLink className="w-5 h-5" />
                    Learn More
                  </span>
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

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

      {/* Tournament Registration Form Modal */}
      <AnimatePresence>
        {showRegistrationForm && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4"
            onClick={closeRegistrationForm}
          >
            <motion.div
              initial={{ scale: 0.5, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.5, opacity: 0 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="bg-dark-secondary border-2 border-neon-green rounded-2xl p-8 max-w-4xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Registration Header */}
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-4">
                  <div className="text-4xl">🎮</div>
                  <div>
                    <h3 className="text-3xl font-audiowide text-white">Tournament Registration</h3>
                    <p className="text-neon-green font-poppins">
                      Join the competition and prove your skills!
                    </p>
                  </div>
                </div>
                <button
                  onClick={closeRegistrationForm}
                  className="text-gray-400 hover:text-white transition-colors text-2xl"
                >
                  <X className="w-6 h-6" />
                </button>
              </div>

              {registrationSuccess ? (
                <div className="text-center py-12">
                  <div className="text-6xl mb-4">🎉</div>
                  <h3 className="text-2xl font-audiowide text-neon-green mb-4">Registration Successful!</h3>
                  <p className="text-gray-300 text-lg">
                    Your tournament registration has been submitted successfully. 
                    We'll review your application and get back to you soon.
                  </p>
                </div>
              ) : (
                <form onSubmit={(e) => { e.preventDefault(); submitRegistration(); }}>
                  {/* Team Information */}
                  <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                      <label className="block text-neon-green font-medium mb-2">Team Name *</label>
                      <input
                        type="text"
                        value={registrationForm.team_name}
                        onChange={(e) => handleFormChange('team_name', e.target.value)}
                        className="w-full bg-dark border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-neon-green focus:outline-none"
                        placeholder="Enter your team name"
                        required
                      />
                    </div>
                    
                    <div>
                      <label className="block text-neon-green font-medium mb-2">Team Type *</label>
                      <select
                        value={registrationForm.team_type}
                        onChange={(e) => handleFormChange('team_type', e.target.value)}
                        className="w-full bg-dark border border-gray-600 rounded-lg px-4 py-3 text-white focus:border-neon-green focus:outline-none"
                        required
                      >
                        <option value="solo">Solo Player</option>
                        <option value="duo">Duo Team (2 Players)</option>
                        <option value="squad">Squad Team (4 Players + 1 Substitute)</option>
                      </select>
                    </div>
                  </div>

                  {/* Captain Information */}
                  <div className="mb-8">
                    <h4 className="text-xl font-audiowide text-white mb-4 flex items-center gap-2">
                      <UserPlus className="w-5 h-5 text-neon-green" />
                      Team Captain Information
                    </h4>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Full Name *</label>
                        <input
                          type="text"
                          value={registrationForm.captain_name}
                          onChange={(e) => handleFormChange('captain_name', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="Captain's full name"
                          required
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Email *</label>
                        <input
                          type="email"
                          value={registrationForm.captain_email}
                          onChange={(e) => handleFormChange('captain_email', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="captain@email.com"
                          required
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Phone Number</label>
                        <input
                          type="tel"
                          value={registrationForm.captain_phone}
                          onChange={(e) => handleFormChange('captain_phone', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="+880 1234-567890"
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Discord Username</label>
                        <input
                          type="text"
                          value={registrationForm.captain_discord}
                          onChange={(e) => handleFormChange('captain_discord', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="username#1234"
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Student ID</label>
                        <input
                          type="text"
                          value={registrationForm.captain_student_id}
                          onChange={(e) => handleFormChange('captain_student_id', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="2020-123-456"
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Department</label>
                        <input
                          type="text"
                          value={registrationForm.captain_department}
                          onChange={(e) => handleFormChange('captain_department', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="Computer Science & Engineering"
                        />
                      </div>
                      
                      <div>
                        <label className="block text-gray-300 text-sm mb-1">Semester</label>
                        <input
                          type="text"
                          value={registrationForm.captain_semester}
                          onChange={(e) => handleFormChange('captain_semester', e.target.value)}
                          className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                          placeholder="8th Semester"
                        />
                      </div>
                      
                                             <div>
                         <label className="block text-gray-300 text-sm mb-1">Game Username *</label>
                         <input
                           type="text"
                           value={registrationForm.captain_game_username}
                           onChange={(e) => handleFormChange('captain_game_username', e.target.value)}
                           className="w-full bg-dark border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                           placeholder="Your game username/ID"
                           required
                         />
                         <p className="text-xs text-gray-500 mt-1">Enter your username/ID in the game (e.g., Valorant username, PUBG player ID)</p>
                       </div>
                    </div>
                  </div>

                  {/* Team Members */}
                  {registrationForm.team_type !== 'solo' && (
                    <div className="mb-8">
                      <div className="flex items-center justify-between mb-4">
                        <h4 className="text-xl font-audiowide text-white flex items-center gap-2">
                          <Users className="w-5 h-5 text-neon-green" />
                          Team Members
                        </h4>
                        <button
                          type="button"
                          onClick={addTeamMember}
                          className="bg-neon-green text-dark px-4 py-2 rounded-lg font-medium hover:bg-green-400 transition-colors"
                        >
                          Add Member
                        </button>
                      </div>
                      
                      <div className="space-y-4">
                        {registrationForm.team_members?.map((member, index) => (
                          <div key={index} className="bg-dark rounded-lg p-4 border border-gray-600">
                            <div className="flex items-center justify-between mb-3">
                              <h5 className="text-neon-green font-medium">Member {index + 1}</h5>
                              <button
                                type="button"
                                onClick={() => removeTeamMember(index)}
                                className="text-red-400 hover:text-red-300"
                              >
                                <X className="w-4 h-4" />
                              </button>
                            </div>
                            
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-3">
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Full Name *</label>
                                <input
                                  type="text"
                                  value={member.player_name}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_name', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="Member's full name"
                                  required
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Email</label>
                                <input
                                  type="email"
                                  value={member.player_email}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_email', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="member@email.com"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Phone Number</label>
                                <input
                                  type="tel"
                                  value={member.player_phone}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_phone', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="+880 1234-567890"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Discord Username</label>
                                <input
                                  type="text"
                                  value={member.player_discord}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_discord', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="username#1234"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Student ID</label>
                                <input
                                  type="text"
                                  value={member.player_student_id}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_student_id', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="2020-123-456"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Department</label>
                                <input
                                  type="text"
                                  value={member.player_department}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_department', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="Computer Science & Engineering"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Semester</label>
                                <input
                                  type="text"
                                  value={member.player_semester}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_semester', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                  placeholder="8th Semester"
                                />
                              </div>
                              
                              <div>
                                <label className="block text-gray-300 text-sm mb-1">Role</label>
                                <select
                                  value={member.player_role}
                                  onChange={(e) => handleTeamMemberChange(index, 'player_role', e.target.value)}
                                  className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                >
                                  <option value="member">Team Member</option>
                                  <option value="substitute">Substitute</option>
                                </select>
                              </div>
                              
                                                             <div>
                                 <label className="block text-gray-300 text-sm mb-1">Game Username *</label>
                                 <input
                                   type="text"
                                   value={member.game_username}
                                   onChange={(e) => handleTeamMemberChange(index, 'game_username', e.target.value)}
                                   className="w-full bg-dark-secondary border border-gray-600 rounded-lg px-3 py-2 text-white focus:border-neon-green focus:outline-none"
                                   placeholder="Member's game username/ID"
                                   required
                                 />
                                 <p className="text-xs text-gray-500 mt-1">Enter member's username/ID in the game</p>
                               </div>
                            </div>
                          </div>
                        ))}
                      </div>
                      
                      <div className="text-sm text-gray-400 mt-2">
                        {registrationForm.team_type === 'duo' && 'Add 1 team member'}
                        {registrationForm.team_type === 'squad' && 'Add 3 team members + 1 substitute'}
                      </div>
                    </div>
                  )}

                  {/* Submit Button */}
                  <div className="flex justify-end gap-4">
                    <button
                      type="button"
                      onClick={closeRegistrationForm}
                      className="px-6 py-3 bg-gray-600 hover:bg-gray-700 text-white rounded-lg transition-colors"
                    >
                      Cancel
                    </button>
                    <button
                      type="submit"
                      disabled={registrationLoading}
                      className="neon-button px-8 py-3 text-lg font-poppins disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                      {registrationLoading ? (
                        <span className="flex items-center gap-2">
                          <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></div>
                          Submitting...
                        </span>
                      ) : (
                        <span className="flex items-center gap-2">
                          <Trophy className="w-5 h-5" />
                          Submit Registration
                        </span>
                      )}
                    </button>
                  </div>
                </form>
              )}
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  )
}

export default GamesPortfolio