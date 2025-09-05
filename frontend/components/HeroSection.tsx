'use client';

import { useEffect, useState } from 'react';
import { motion } from 'framer-motion';
import { TypeAnimation } from 'react-type-animation';
import { Play, Users, Trophy, Gamepad2 } from 'lucide-react';
import CountUp from 'react-countup';
import { apiService, Stats } from '@/services/api';

export default function HeroSection() {
  const [stats, setStats] = useState<Stats>({
    tournaments: 0,
    players: 0,
    games: 0,
    events: 0,
    members: 0,
    gallery: 0,
    sponsors: 0
  });
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    // Fetch real-time stats from database
    const fetchStats = async () => {
      try {
        const data = await apiService.getStats();
        setStats(data);
      } catch (error) {
        console.error('Failed to fetch stats:', error);
        // Use default values if API fails
        setStats({
          tournaments: 35,
          players: 2000,
          games: 7,
          events: 50,
          members: 25,
          gallery: 100,
          sponsors: 15
        });
      } finally {
        setLoading(false);
      }
    };

    fetchStats();

    // Set up interval to refresh stats every 30 seconds
    const interval = setInterval(fetchStats, 30000);

    return () => clearInterval(interval);
  }, []);



  return (
    <section className="relative min-h-screen flex items-center justify-center overflow-hidden">
      {/* Background Elements */}
      <div className="absolute inset-0 cyber-grid opacity-20"></div>
      
      {/* Main Content */}
      <div className="relative z-10 text-center px-4 max-w-6xl mx-auto">
        {/* Logo */}
        <motion.div
          initial={{ y: -50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, ease: "easeOut" }}
          className="mb-8"
        >
          <img
            src="https://res.cloudinary.com/dn7ucxk8a/image/upload/v1757097048/file_00000000a118622fa2869ebff1ccc94e_fzyw89.png"
            alt="DIU Esports Logo"
            className="w-48 h-auto mx-auto drop-shadow-2xl"
          />
        </motion.div>

        {/* Main Heading */}
        <motion.h1
          initial={{ y: 50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.3 }}
          className="text-6xl md:text-8xl font-orbitron font-bold mb-6 neon-text glitch-text"
          data-text="DIU ESPORTS COMMUNITY"
        >
          DIU ESPORTS COMMUNITY
        </motion.h1>

        {/* Typing Slogan */}
        <motion.div
          initial={{ y: 50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.6 }}
          className="text-2xl md:text-3xl font-audiowide text-neon mb-8"
        >
          <TypeAnimation
            sequence={[
              'One Community...',
              1000,
              'One University...',
              1000,
              'Endless Gaming Battles!',
              1000,
            ]}
            wrapper="span"
            speed={50}
            repeat={Infinity}
            className="neon-text"
          />
        </motion.div>

        {/* CTA Buttons */}
        <motion.div
          initial={{ y: 50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 0.9 }}
          className="flex flex-col sm:flex-row gap-4 justify-center mb-12"
        >
          <a 
            href="#games" 
            className="neon-button group inline-flex items-center justify-center"
            onClick={(e) => {
              e.preventDefault();
              document.getElementById('games')?.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
              });
            }}
          >
            <Play className="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
            Explore Tournaments
          </a>
          <a 
            href="#gallery" 
            className="neon-button group inline-flex items-center justify-center"
            onClick={(e) => {
              e.preventDefault();
              document.getElementById('gallery')?.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
              });
            }}
          >
            <Users className="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
            View Gallery
          </a>
          <a 
            href="#events" 
            className="neon-button group inline-flex items-center justify-center"
            onClick={(e) => {
              e.preventDefault();
              document.getElementById('events')?.scrollIntoView({ 
                behavior: 'smooth',
                block: 'start'
              });
            }}
          >
            <Trophy className="w-5 h-5 mr-2 group-hover:scale-110 transition-transform" />
            Next Event
          </a>
        </motion.div>

        {/* Colorful Animated Counters */}
        <motion.div
          initial={{ y: 50, opacity: 0 }}
          animate={{ y: 0, opacity: 1 }}
          transition={{ duration: 0.8, delay: 1.2 }}
          className="grid grid-cols-3 gap-6 max-w-2xl mx-auto"
        >
          {/* Tournaments Counter */}
          <motion.div 
            className="text-center group"
            whileHover={{ scale: 1.05 }}
            transition={{ type: "spring", stiffness: 300 }}
          >
            <div className="relative">
              <div className="absolute inset-0 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
              <div className="relative bg-gradient-to-br from-yellow-400 to-orange-500 rounded-lg p-4 border-2 border-yellow-300 shadow-lg">
                <Trophy className="w-8 h-8 mx-auto mb-2 text-white" />
                <div className="text-3xl font-orbitron font-bold text-white">
                  {!loading && (
                    <CountUp
                      end={stats.tournaments}
                      duration={2.5}
                      delay={0}
                      separator=","
                      suffix="+"
                    />
                  )}
                  {loading && "35+"}
                </div>
                <div className="text-sm text-yellow-100 font-medium">Tournaments</div>
              </div>
            </div>
          </motion.div>

          {/* Players Counter */}
          <motion.div 
            className="text-center group"
            whileHover={{ scale: 1.05 }}
            transition={{ type: "spring", stiffness: 300 }}
          >
            <div className="relative">
              <div className="absolute inset-0 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
              <div className="relative bg-gradient-to-br from-blue-400 to-purple-500 rounded-lg p-4 border-2 border-blue-300 shadow-lg">
                <Users className="w-8 h-8 mx-auto mb-2 text-white" />
                <div className="text-3xl font-orbitron font-bold text-white">
                  {!loading && (
                    <CountUp
                      end={stats.players}
                      duration={2.5}
                      delay={0.2}
                      separator=","
                      suffix="+"
                    />
                  )}
                  {loading && "2000+"}
                </div>
                <div className="text-sm text-blue-100 font-medium">Players</div>
              </div>
            </div>
          </motion.div>

          {/* Games Counter */}
          <motion.div 
            className="text-center group"
            whileHover={{ scale: 1.05 }}
            transition={{ type: "spring", stiffness: 300 }}
          >
            <div className="relative">
              <div className="absolute inset-0 bg-gradient-to-r from-green-400 to-teal-500 rounded-lg blur-lg opacity-30 group-hover:opacity-50 transition-opacity"></div>
              <div className="relative bg-gradient-to-br from-green-400 to-teal-500 rounded-lg p-4 border-2 border-green-300 shadow-lg">
                <Gamepad2 className="w-8 h-8 mx-auto mb-2 text-white" />
                <div className="text-3xl font-orbitron font-bold text-white">
                  {!loading && (
                    <CountUp
                      end={stats.games}
                      duration={2.5}
                      delay={0.4}
                      separator=","
                      suffix="+"
                    />
                  )}
                  {loading && "7+"}
                </div>
                <div className="text-sm text-green-100 font-medium">Games</div>
              </div>
            </div>
          </motion.div>
        </motion.div>
      </div>
    </section>
  );
}
