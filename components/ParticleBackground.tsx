'use client'

const ParticleBackground = () => {

  return (
    <div className="fixed inset-0 z-0 pointer-events-none">
      <div className="absolute inset-0 cyber-grid opacity-20" />
      <div className="absolute inset-0 bg-gradient-to-br from-dark via-dark-secondary to-primary-blue-dark opacity-80" />
      
      {/* Floating neon particles */}
      <div className="absolute inset-0">
        {[...Array(20)].map((_, i) => (
          <div
            key={i}
            className="absolute w-1 h-1 bg-neon-green rounded-full animate-pulse-glow"
            style={{
              left: `${Math.random() * 100}%`,
              top: `${Math.random() * 100}%`,
              animationDelay: `${Math.random() * 2}s`,
              animationDuration: `${2 + Math.random() * 2}s`,
            }}
          />
        ))}
      </div>

      {/* Animated grid lines */}
      <div className="absolute inset-0 overflow-hidden">
        {[...Array(5)].map((_, i) => (
          <div
            key={i}
            className="absolute w-full h-px bg-gradient-to-r from-transparent via-primary-blue to-transparent opacity-30"
            style={{
              top: `${20 + i * 20}%`,
              animation: `slide-right ${8 + i * 2}s linear infinite`,
            }}
          />
        ))}
        {[...Array(5)].map((_, i) => (
          <div
            key={i}
            className="absolute h-full w-px bg-gradient-to-b from-transparent via-primary-blue to-transparent opacity-30"
            style={{
              left: `${20 + i * 20}%`,
              animation: `slide-down ${8 + i * 2}s linear infinite`,
            }}
          />
        ))}
      </div>

      {/* Glowing orbs */}
      <div className="absolute top-1/4 left-1/4 w-32 h-32 bg-neon-green rounded-full opacity-20 blur-xl animate-pulse" />
      <div className="absolute top-3/4 right-1/4 w-24 h-24 bg-primary-blue rounded-full opacity-20 blur-xl animate-pulse" />
      <div className="absolute top-1/2 left-1/2 w-16 h-16 bg-cyber-neon-purple rounded-full opacity-20 blur-xl animate-pulse" />
    </div>
  )
}

export default ParticleBackground
