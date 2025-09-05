'use client'

import { useState } from 'react'
import { motion } from 'framer-motion'
import { Mail, Phone, MapPin, Send, Users } from 'lucide-react'
import Image from 'next/image'

const ContactFooter = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    subject: '',
    message: ''
  })

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    })
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()
    // Handle form submission
    console.log('Form submitted:', formData)
  }

  const socialLinks = [
    { name: 'Discord', icon: '💬', url: 'https://discord.gg/QaFeGR5akv', color: 'neon-green' },
    { name: 'Twitch', icon: '📺', url: 'https://twitch.tv/diuesports', color: 'cyber-neon-purple' },
    { name: 'Facebook', icon: '📘', url: 'https://facebook.com/diuesports', color: 'primary-blue' },
    { name: 'YouTube', icon: '📹', url: 'https://youtube.com/diuesports', color: 'cyber-neon-pink' },
    { name: 'Twitter', icon: '🐦', url: 'https://twitter.com/diuesports', color: 'neon-green' },
    { name: 'Instagram', icon: '📷', url: 'https://instagram.com/diuesports', color: 'cyber-neon-pink' },
  ]

  const contactInfo = [
    { icon: <Mail className="w-5 h-5" />, label: 'Email', value: 'diuesports@gmail.com', color: 'neon-green' },
    { icon: <Phone className="w-5 h-5" />, label: 'Phone', value: '+8801737183436', color: 'primary-blue' },
    { icon: <MapPin className="w-5 h-5" />, label: 'Address', value: 'DIU Campus, Dhaka, Bangladesh', color: 'cyber-neon-purple' },
  ]

  return (
    <div className="container mx-auto px-4">
      {/* Contact Section */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8 }}
        viewport={{ once: true }}
        className="mb-20"
      >
        <div className="text-center mb-16">
          <h2 className="text-4xl md:text-6xl font-audiowide text-white mb-6 neon-text">
            Get In Touch
          </h2>
          <p className="text-xl text-gray-300 font-poppins max-w-3xl mx-auto">
            Ready to join our esports community? Reach out to us for any questions or collaborations
          </p>
        </div>

        <div className="grid lg:grid-cols-2 gap-16 max-w-7xl mx-auto">
          {/* Contact Form */}
          <motion.div
            initial={{ opacity: 0, x: -50 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.2 }}
            viewport={{ once: true }}
          >
            <div className="bg-dark-secondary border-2 border-neon-green rounded-2xl p-8 backdrop-blur-sm">
              <h3 className="text-2xl font-russo text-white mb-6 text-center neon-text">
                Send us a Message
              </h3>

              <form onSubmit={handleSubmit} className="space-y-6">
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label className="block text-sm font-poppins text-gray-300 mb-2">
                      Name
                    </label>
                    <input
                      type="text"
                      name="name"
                      value={formData.name}
                      onChange={handleInputChange}
                      className="w-full px-4 py-3 bg-dark border-2 border-gray-600 rounded-lg text-white font-poppins
                               focus:border-neon-green focus:outline-none transition-all duration-300
                               focus:shadow-neon placeholder-gray-500"
                      placeholder="Your Name"
                      required
                    />
                  </div>
                  <div>
                    <label className="block text-sm font-poppins text-gray-300 mb-2">
                      Email
                    </label>
                    <input
                      type="email"
                      name="email"
                      value={formData.email}
                      onChange={handleInputChange}
                      className="w-full px-4 py-3 bg-dark border-2 border-gray-600 rounded-lg text-white font-poppins
                               focus:border-neon-green focus:outline-none transition-all duration-300
                               focus:shadow-neon placeholder-gray-500"
                      placeholder="your@email.com"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label className="block text-sm font-poppins text-gray-300 mb-2">
                    Subject
                  </label>
                  <input
                    type="text"
                    name="subject"
                    value={formData.subject}
                    onChange={handleInputChange}
                    className="w-full px-4 py-3 bg-dark border-2 border-gray-600 rounded-lg text-white font-poppins
                             focus:border-neon-green focus:outline-none transition-all duration-300
                             focus:shadow-neon placeholder-gray-500"
                    placeholder="What's this about?"
                    required
                  />
                </div>

                <div>
                  <label className="block text-sm font-poppins text-gray-300 mb-2">
                    Message
                  </label>
                  <textarea
                    name="message"
                    value={formData.message}
                    onChange={handleInputChange}
                    rows={5}
                    className="w-full px-4 py-3 bg-dark border-2 border-gray-600 rounded-lg text-white font-poppins
                             focus:border-neon-green focus:outline-none transition-all duration-300
                             focus:shadow-neon placeholder-gray-500 resize-none"
                    placeholder="Tell us more..."
                    required
                  />
                </div>

                <button
                  type="submit"
                  className="neon-button w-full group"
                >
                  <span className="flex items-center justify-center gap-2">
                    <Send className="w-5 h-5 group-hover:animate-bounce" />
                    Send Message
                  </span>
                </button>
              </form>
            </div>
          </motion.div>

          {/* Contact Information */}
          <motion.div
            initial={{ opacity: 0, x: 50 }}
            whileInView={{ opacity: 1, x: 0 }}
            transition={{ duration: 0.8, delay: 0.4 }}
            viewport={{ once: true }}
            className="space-y-8"
          >
            {/* Contact Details */}
            <div className="space-y-6">
              {contactInfo.map((info, index) => (
                <motion.div
                  key={index}
                  initial={{ opacity: 0, y: 20 }}
                  whileInView={{ opacity: 1, y: 0 }}
                  transition={{ duration: 0.6, delay: 0.6 + index * 0.1 }}
                  viewport={{ once: true }}
                  className="flex items-start gap-4 group"
                >
                  <div className={`p-3 rounded-lg bg-opacity-20 backdrop-blur-sm border-2 transition-all duration-300 group-hover:scale-110 ${
                    info.color === 'neon-green' ? 'bg-neon-green border-neon-green' :
                    info.color === 'primary-blue' ? 'bg-primary-blue border-primary-blue' :
                    'bg-cyber-neon-purple border-cyber-neon-purple'
                  }`}>
                    <div className={`${
                      info.color === 'neon-green' ? 'text-neon-green' :
                      info.color === 'primary-blue' ? 'text-primary-blue' :
                      'text-cyber-neon-purple'
                    }`}>
                      {info.icon}
                    </div>
                  </div>
                  <div>
                    <h4 className="text-lg font-russo text-white mb-1">{info.label}</h4>
                    <p className="text-gray-300 font-poppins">{info.value}</p>
                  </div>
                </motion.div>
              ))}
            </div>

            {/* Social Media Links */}
            <div>
              <h4 className="text-xl font-russo text-white mb-6 text-center">Follow Us</h4>
              <div className="grid grid-cols-3 gap-4">
                {socialLinks.map((social, index) => (
                  <motion.a
                    key={index}
                    href={social.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    initial={{ opacity: 0, scale: 0.8 }}
                    whileInView={{ opacity: 1, scale: 1 }}
                    transition={{ duration: 0.6, delay: 0.8 + index * 0.1 }}
                    viewport={{ once: true }}
                    className="group"
                  >
                    <div className={`p-4 rounded-xl border-2 bg-dark-secondary bg-opacity-50 backdrop-blur-sm
                                   transition-all duration-300 hover:scale-105 text-center ${
                      social.color === 'neon-green' ? 'border-neon-green hover:shadow-neon' :
                      social.color === 'primary-blue' ? 'border-primary-blue hover:shadow-neon-blue' :
                      social.color === 'cyber-neon-purple' ? 'border-cyber-neon-purple hover:shadow-neon' :
                      'border-cyber-neon-pink hover:shadow-neon'
                    }`}>
                      <div className="text-2xl mb-2 group-hover:animate-bounce transition-all duration-300">
                        {social.icon}
                      </div>
                      <div className="text-xs font-poppins text-gray-300 uppercase tracking-wider">
                        {social.name}
                      </div>
                    </div>
                  </motion.a>
                ))}
              </div>
            </div>
          </motion.div>
        </div>
      </motion.div>

      {/* Footer */}
      <motion.div
        initial={{ opacity: 0, y: 30 }}
        whileInView={{ opacity: 1, y: 0 }}
        transition={{ duration: 0.8, delay: 0.6 }}
        viewport={{ once: true }}
        className="border-t-2 border-neon-green"
      >


        {/* About Us Prominent Section */}
        <div className="py-12 border-t border-neon-green border-opacity-30">
          <div className="max-w-4xl mx-auto text-center">
            <motion.div
              initial={{ opacity: 0, y: 30 }}
              whileInView={{ opacity: 1, y: 0 }}
              transition={{ duration: 0.8, delay: 0.4 }}
              viewport={{ once: true }}
              className="mb-8"
            >
              <h3 className="text-3xl font-audiowide text-white mb-4">
                Want to Learn More About DIU Esports?
              </h3>
              <p className="text-lg text-gray-300 font-poppins mb-6">
                Discover our story, mission, and the amazing community we've built together.
              </p>
              <a
                href="/about"
                className="neon-button group inline-flex items-center justify-center text-lg px-8 py-4"
              >
                <Users className="w-6 h-6 mr-3 group-hover:scale-110 transition-transform" />
                About Us
              </a>
            </motion.div>
          </div>
        </div>

        {/* Bottom Footer */}
        <div className="bg-gradient-to-r from-neon-green via-primary-blue to-cyber-neon-purple py-8">
          <div className="max-w-6xl mx-auto px-4">
            <div className="grid md:grid-cols-2 gap-8 items-center">
              {/* Logo and Description */}
              <div className="text-center md:text-left">
                <div className="flex items-center justify-center md:justify-start gap-4 mb-4">
                  <div className="w-16 h-16 bg-white rounded-full flex items-center justify-center border-2 border-white shadow-lg">
                    <Image
                      src="/logo.png"
                      alt="DIU Esports Logo"
                      width={48}
                      height={48}
                      className="object-contain rounded-full"
                      priority
                    />
                  </div>
                  <div>
                    <h3 className="text-xl font-audiowide text-white">DIU ESPORTS</h3>
                    <p className="text-sm text-white font-poppins">Community</p>
                  </div>
                </div>
                <p className="text-white text-sm font-poppins opacity-90">
                  Building the future of university esports, one champion at a time.
                </p>
              </div>

              {/* Copyright and Links */}
              <div className="text-center md:text-right">
                <div className="flex items-center justify-center md:justify-end gap-4 mb-4">
                  <a href="#" className="text-white hover:text-neon-green transition-colors duration-300 text-sm">
                    Privacy Policy
                  </a>
                  <a href="#" className="text-white hover:text-neon-green transition-colors duration-300 text-sm">
                    Terms of Service
                  </a>
                  <a href="#" className="text-white hover:text-neon-green transition-colors duration-300 text-sm">
                    Cookie Policy
                  </a>
                </div>
                <p className="text-white text-sm font-poppins opacity-90">
                  © 2025 DIU Esports Community. All rights reserved.
                </p>
              </div>
            </div>
          </div>
        </div>
      </motion.div>
    </div>
  )
}

export default ContactFooter
