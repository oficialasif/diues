'use client'

import { useState, useEffect } from 'react'
import { motion, AnimatePresence } from 'framer-motion'
import { Gamepad2, Trophy, Users, Calendar, ExternalLink, Play, Award, ChevronDown, ChevronUp } from 'lucide-react'
import { apiService, Tournament } from '@/services/api'

interface Game {
  id: number
  name: string
  genre: string
  tournaments: number
  players: number
  image: string
  description: string
  achievements: string[]
  highlights: string[]
}

const GamesPortfolio = () => {
  const [selectedGame, setSelectedGame] = useState<Game | null>(null)
  const [tournaments, setTournaments] = useState<Tournament[]>([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)
  const [showAllGames, setShowAllGames] = useState(false)

  // Fetch tournaments from API
  useEffect(() => {
    const fetchTournaments = async () => {
      try {
        setLoading(true)
        const data = await apiService.getTournaments()
        setTournaments(data)
        setError(null)
      } catch (err) {
        console.error('Failed to fetch tournaments:', err)
        setError('Failed to load tournaments')
      } finally {
        setLoading(false)
      }
    }

    fetchTournaments()
  }, [])

  // Group tournaments by game and calculate stats
  const getGameStats = (gameName: string) => {
    const gameTournaments = tournaments.filter(t => 
      t.game_name.toLowerCase() === gameName.toLowerCase()
    )
    
    const totalParticipants = gameTournaments.reduce((sum, t) => sum + t.current_participants, 0)
    
    return {
      tournaments: gameTournaments.length,
      players: totalParticipants
    }
  }

  const games: Game[] = [
    {
      id: 1,
      name: 'Valorant',
      genre: 'FPS Tactical',
      tournaments: getGameStats('Valorant').tournaments,
      players: getGameStats('Valorant').players,
      image: '🎯',
      description: 'Our most competitive title with strategic gameplay and team coordination.',
      achievements: ['Regional Champions 2023', 'University League Winners', 'Pro Player Development'],
      highlights: ['5v5 Tactical Matches', 'Strategic Team Play', 'High Skill Ceiling'],
    },
    {
      id: 2,
      name: 'League of Legends',
      genre: 'MOBA',
      tournaments: getGameStats('League of Legends').tournaments,
      players: getGameStats('League of Legends').players,
      image: '⚔️',
      description: 'Classic MOBA action with intense team fights and strategic depth.',
      achievements: ['Inter-University Champions', 'Season Winners', 'Community Favorites'],
      highlights: ['5v5 Team Battles', 'Strategic Objectives', 'Champion Mastery'],
    },
    {
      id: 3,
      name: 'CS:GO',
      genre: 'FPS',
      tournaments: getGameStats('CS:GO').tournaments,
      players: getGameStats('CS:GO').players,
      image: '🔫',
      description: 'Fast-paced tactical shooter with precision and teamwork.',
      achievements: ['Tournament Runners-up', 'Skill Development Program', 'Community Growth'],
      highlights: ['5v5 Tactical Matches', 'Precision Shooting', 'Team Coordination'],
    },
    {
      id: 4,
      name: 'Dota 2',
      genre: 'MOBA',
      tournaments: getGameStats('Dota 2').tournaments,
      players: getGameStats('Dota 2').players,
      image: '🗡️',
      description: 'Complex strategy game with deep mechanics and team synergy.',
      achievements: ['Strategy Champions', 'Innovation Award', 'Learning Excellence'],
      highlights: ['5v5 Strategic Battles', 'Complex Mechanics', 'Team Synergy'],
    },
    {
      id: 5,
      name: 'FIFA',
      genre: 'Sports',
      tournaments: getGameStats('FIFA').tournaments,
      players: getGameStats('FIFA').players,
      image: '⚽',
      description: 'Football simulation with competitive leagues and tournaments.',
      achievements: ['Sports Category Winners', 'Community Engagement', 'Regular Tournaments'],
      highlights: ['1v1 Matches', 'League Competitions', 'Seasonal Events'],
    },
    {
      id: 6,
      name: 'PUBG Mobile',
      genre: 'Battle Royale',
      tournaments: getGameStats('PUBG Mobile').tournaments,
      players: getGameStats('PUBG Mobile').players,
      image: '🎮',
      description: 'Mobile battle royale with squad-based competitive play.',
      achievements: ['Mobile Gaming Champions', 'Squad Excellence', 'Innovation Award'],
      highlights: ['Squad Battles', 'Mobile Gaming', 'Battle Royale'],
    },
    {
      id: 7,
      name: 'Rocket League',
      genre: 'Sports Racing',
      tournaments: getGameStats('Rocket League').tournaments,
      players: getGameStats('Rocket League').players,
      image: '🚗',
      description: 'High-octane car soccer with aerial acrobatics.',
      achievements: ['Racing Champions', 'Skill Development', 'Community Favorites'],
      highlights: ['3v3 Matches', 'Aerial Skills', 'Fast-Paced Action'],
    },
  ]

  // Get games to display based on showAllGames state
  const displayedGames = showAllGames ? games : games.slice(0, 3)

  const openModal = (game: Game) => {
    setSelectedGame(game)
  }

  const closeModal = () => {
    setSelectedGame(null)
  }

  const toggleShowAll = () => {
    setShowAllGames(!showAllGames)
  }

  if (loading) {
    return (
      <div className="container mx-auto px-4 py-20">
        <div className="text-center">
          <div className="animate-spin rounded-full h-32 w-32 border-b-2 border-neon-green mx-auto"></div>
          <p className="text-white mt-4">Loading tournaments...</p>
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
          Games & Tournaments
        </h2>
        <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
          Discover our competitive gaming portfolio and tournament achievements
        </p>
        {tournaments.length > 0 && (
          <p className="text-neon-green mt-4 font-poppins">
            {tournaments.length} active tournaments available
          </p>
        )}
      </motion.div>

      {/* Games Grid */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
        {displayedGames.map((game, index) => (
          <motion.div
            key={game.id}
            initial={{ opacity: 0, y: 50 }}
            whileInView={{ opacity: 1, y: 0 }}
            transition={{ duration: 0.6, delay: index * 0.1 }}
            viewport={{ once: true }}
            className="group"
          >
            <div 
              className="angled-card cursor-pointer bg-dark-secondary border-2 border-neon-green rounded-xl p-6 
                         hover:border-neon-green hover:shadow-neon transition-all duration-300
                         hover:scale-105 hover:rotate-0"
              onClick={() => openModal(game)}
            >
              {/* Game Icon */}
              <div className="text-center mb-6">
                <div className="text-6xl mb-4">{game.image}</div>
                <h3 className="text-2xl font-russo text-white mb-2">{game.name}</h3>
                <p className="text-sm text-neon-green font-poppins uppercase tracking-wider">
                  {game.genre}
                </p>
              </div>

              {/* Game Stats */}
              <div className="space-y-3 mb-6">
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-400 flex items-center gap-2">
                    <Trophy className="w-4 h-4" />
                    Tournaments
                  </span>
                  <span className="text-white font-orbitron">{game.tournaments}</span>
                </div>
                <div className="flex items-center justify-between text-sm">
                  <span className="text-gray-400 flex items-center gap-2">
                    <Users className="w-4 h-4" />
                    Players
                  </span>
                  <span className="text-white font-orbitron">{game.players}</span>
                </div>
              </div>

              {/* Hover Effect */}
              <div className="opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-center">
                <div className="flex items-center justify-center gap-2 text-neon-green font-poppins">
                  <Play className="w-4 h-4" />
                  Click to Explore
                </div>
              </div>

              {/* Glowing Border Effect */}
              <div className="absolute inset-0 rounded-xl border-2 border-transparent group-hover:border-neon-green transition-all duration-300 opacity-0 group-hover:opacity-100" />
            </div>
          </motion.div>
        ))}
      </div>

      {/* Show More/Less Button */}
      {games.length > 3 && (
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
              {showAllGames ? (
                <>
                  <ChevronUp className="w-5 h-5 group-hover:animate-bounce" />
                  Show Less
                </>
              ) : (
                <>
                  <ChevronDown className="w-5 h-5 group-hover:animate-bounce" />
                  Show More Games
                </>
              )}
            </span>
            <div className="mt-2 text-sm text-gray-400">
              {showAllGames ? 'Collapse to 3 games' : `${games.length - 3} more games available`}
            </div>
          </motion.button>
        </motion.div>
      )}

      {/* Game Details Modal */}
      <AnimatePresence>
        {selectedGame && (
          <motion.div
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            className="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4"
            onClick={closeModal}
          >
            <motion.div
              initial={{ scale: 0.5, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              exit={{ scale: 0.5, opacity: 0 }}
              transition={{ type: "spring", damping: 25, stiffness: 300 }}
              className="bg-dark-secondary border-2 border-neon-green rounded-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto"
              onClick={(e) => e.stopPropagation()}
            >
              {/* Modal Header */}
              <div className="flex items-center justify-between mb-6">
                <div className="flex items-center gap-4">
                  <div className="text-4xl">{selectedGame.image}</div>
                  <div>
                    <h3 className="text-3xl font-audiowide text-white">{selectedGame.name}</h3>
                    <p className="text-neon-green font-poppins uppercase tracking-wider">
                      {selectedGame.genre}
                    </p>
                  </div>
                </div>
                <button
                  onClick={closeModal}
                  className="text-gray-400 hover:text-white transition-colors"
                >
                  ✕
                </button>
              </div>

              {/* Game Description */}
              <div className="mb-6">
                <p className="text-gray-300 font-poppins leading-relaxed">
                  {selectedGame.description}
                </p>
              </div>

              {/* Game Stats */}
              <div className="grid grid-cols-2 gap-4 mb-6">
                <div className="bg-dark rounded-lg p-4 text-center border border-neon-green">
                  <div className="text-2xl font-orbitron text-neon-green">{selectedGame.tournaments}</div>
                  <div className="text-sm text-gray-400">Tournaments</div>
                </div>
                <div className="bg-dark rounded-lg p-4 text-center border border-primary-blue">
                  <div className="text-2xl font-orbitron text-primary-blue">{selectedGame.players}</div>
                  <div className="text-sm text-gray-400">Players</div>
                </div>
              </div>

              {/* Achievements */}
              <div className="mb-6">
                <h4 className="text-xl font-russo text-neon-green mb-3 flex items-center gap-2">
                  <Award className="w-5 h-5" />
                  Achievements
                </h4>
                <div className="space-y-2">
                  {selectedGame.achievements.map((achievement, index) => (
                    <div key={index} className="flex items-center gap-2 text-gray-300">
                      <div className="w-2 h-2 bg-neon-green rounded-full" />
                      {achievement}
                    </div>
                  ))}
                </div>
              </div>

              {/* Highlights */}
              <div className="mb-6">
                <h4 className="text-xl font-russo text-primary-blue mb-3 flex items-center gap-2">
                  <Gamepad2 className="w-5 h-5" />
                  Game Highlights
                </h4>
                <div className="space-y-2">
                  {selectedGame.highlights.map((highlight, index) => (
                    <div key={index} className="flex items-center gap-2 text-gray-300">
                      <div className="w-2 h-2 bg-primary-blue rounded-full" />
                      {highlight}
                    </div>
                  ))}
                </div>
              </div>

              {/* Action Buttons */}
              <div className="flex gap-4">
                <button className="neon-button flex-1">
                  <span className="flex items-center justify-center gap-2">
                    <Trophy className="w-5 h-5" />
                    View Tournaments
                  </span>
                </button>
                <button className="neon-button flex-1">
                  <span className="flex items-center justify-center gap-2">
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
    </div>
  )
}

export default GamesPortfolio
