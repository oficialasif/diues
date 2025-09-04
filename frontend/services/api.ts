

// API Configuration - Environment aware
let API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 
  (process.env.NODE_ENV === 'production' 
    ? 'https://diu-esports-backend.onrender.com'
    : 'http://localhost:8080'
  );

// Remove /api suffix if it exists (for backward compatibility)
if (API_BASE_URL.endsWith('/api')) {
  API_BASE_URL = API_BASE_URL.replace('/api', '');
}

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data: T;
  timestamp: string;
}

export interface Tournament {
  id: number;
  name: string;
  game_id: number;
  game_name: string;
  genre: string;
  description: string;
  start_date: string;
  end_date: string;
  prize_pool: number;
  max_participants: number;
  current_participants: number;
  status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
  poster_url: string;
  created_at: string;
  updated_at: string;
}

export interface Event {
  id: number;
  title: string;
  event_type: 'tournament' | 'workshop' | 'meetup' | 'celebration';
  description: string;
  event_date: string;
  location: string;
  status: 'upcoming' | 'ongoing' | 'completed' | 'cancelled';
  poster_url: string;
  created_at: string;
  updated_at: string;
}

export interface CommitteeMember {
  id: number;
  name: string;
  role: string;
  position: string;
  image_url: string;
  bio: string;
  achievements: string;
  social_links: string;
  is_current: boolean;
  year: string;
  order_index: number;
  created_at: string;
  updated_at: string;
}

export interface GalleryItem {
  id: number;
  title: string;
  description: string;
  category: string;
  year: string;
  tags: string;
  image_url: string;
  video_url: string;
  created_at: string;
  updated_at: string;
}

export interface Sponsor {
  id: number;
  name: string;
  logo_url: string;
  category: string;
  partnership_type: string;
  website_url: string;
  benefits: string;
  description?: string;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface Achievement {
  id: number;
  title: string;
  description: string;
  category: string;
  year: string;
  icon_url: string;
  highlights_url: string;
  created_at: string;
  updated_at: string;
}

export interface SiteSettings {
  id: number;
  site_title: string;
  site_description: string;
  contact_email: string;
  contact_phone: string;
  address: string;
  social_links: string;
  created_at: string;
  updated_at: string;
}

export interface Stats {
  tournaments: number;
  players: number;
  games: number;
  events: number;
  members: number;
  gallery: number;
  sponsors: number;
}

export interface CountdownSettings {
  id?: number;
  status_text: string;
  custom_message?: string;
  target_date: string;
  is_active: boolean;
  show_countdown: boolean;
  countdown_type: 'days' | 'hours' | 'minutes' | 'seconds';
  created_at?: string;
  updated_at?: string;
}

export interface TournamentRegistration {
  id: number;
  tournament_id: number;
  team_name: string;
  team_type: 'solo' | 'duo' | 'squad';
  captain_name: string;
  captain_email: string;
  captain_phone?: string;
  captain_discord?: string;
  captain_student_id?: string;
  captain_department?: string;
  captain_semester?: string;
  status: 'pending' | 'approved' | 'rejected';
  registration_date: string;
  notes?: string;
  tournament_name?: string;
  game_name?: string;
  genre?: string;
  team_members?: TournamentTeamMember[];
}

export interface TournamentTeamMember {
  id: number;
  registration_id: number;
  player_name: string;
  player_email?: string;
  player_phone?: string;
  player_discord?: string;
  player_student_id?: string;
  player_department?: string;
  player_semester?: string;
  player_role: 'captain' | 'member' | 'substitute';
  game_username: string;
  created_at: string;
}

export interface TournamentRegistrationForm {
  tournament_id: number;
  team_name: string;
  team_type: 'solo' | 'duo' | 'squad';
  captain_name: string;
  captain_email: string;
  captain_phone?: string;
  captain_discord?: string;
  captain_student_id?: string;
  captain_department?: string;
  captain_semester?: string;
  captain_game_username: string;
  team_members?: TournamentTeamMember[];
}

class ApiService {
  private async request<T>(endpoint: string, options: RequestInit = {}): Promise<ApiResponse<T>> {
    const url = `${API_BASE_URL}/${endpoint}`;
    
    const defaultOptions: RequestInit = {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers,
      },
      ...options,
    };

    try {
      console.log('Making API request to:', url);
      const response = await fetch(url, defaultOptions);
      console.log('API response status:', response.status);
      
      const data = await response.json();
      console.log('API response data:', data);
      
      if (!response.ok) {
        throw new Error(data.message || `HTTP error! status: ${response.status}`);
      }
      
      return data;
    } catch (error) {
      console.error('API request failed:', error);
      console.error('Request URL:', url);
      console.error('Request options:', defaultOptions);
      throw error;
    }
  }

  // Tournaments
  async getTournaments(): Promise<Tournament[]> {
    const response = await this.request<Tournament[]>('tournaments.php');
    return response.data;
  }

  async getTournament(id: number): Promise<Tournament> {
    const response = await this.request<Tournament>(`tournaments/${id}`);
    return response.data;
  }

  // Tournament Registrations
  async registerForTournament(registration: TournamentRegistrationForm): Promise<TournamentRegistration> {
    console.log('API Service: Sending registration request:', registration);
    const response = await this.request<TournamentRegistration>('tournaments/register', {
      method: 'POST',
      body: JSON.stringify(registration)
    });
    console.log('API Service: Received response:', response);
    return response.data;
  }

  async getTournamentRegistrations(tournamentId: number): Promise<TournamentRegistration[]> {
    const response = await this.request<TournamentRegistration[]>(`tournaments/${tournamentId}/registrations`);
    return response.data;
  }

  async getAllTournamentRegistrations(): Promise<TournamentRegistration[]> {
    const response = await this.request<TournamentRegistration[]>('tournaments/registrations');
    return response.data;
  }

  // Events
  async getEvents(): Promise<Event[]> {
    const response = await this.request<Event[]>('events.php');
    return response.data;
  }

  async getEvent(id: number): Promise<Event> {
    const response = await this.request<Event>(`events/${id}`);
    return response.data;
  }

  // Committee
  async getCommitteeMembers(): Promise<CommitteeMember[]> {
    const response = await this.request<CommitteeMember[]>('committee.php');
    return response.data;
  }

  async getCommitteeMember(id: number): Promise<CommitteeMember> {
    const response = await this.request<CommitteeMember>(`committee/${id}`);
    return response.data;
  }

  // Gallery
  async getGalleryItems(): Promise<GalleryItem[]> {
    const response = await this.request<GalleryItem[]>('gallery.php');
    return response.data;
  }

  async getGalleryItem(id: number): Promise<GalleryItem> {
    const response = await this.request<GalleryItem>(`gallery/${id}`);
    return response.data;
  }

  // Sponsors
  async getSponsors(): Promise<Sponsor[]> {
    const response = await this.request<Sponsor[]>('sponsors.php');
    return response.data;
  }

  async getSponsor(id: number): Promise<Sponsor> {
    const response = await this.request<Sponsor>(`sponsors/${id}`);
    return response.data;
  }

  // Achievements
  async getAchievements(): Promise<Achievement[]> {
    const response = await this.request<Achievement[]>('achievements');
    return response.data;
  }

  async getAchievement(id: number): Promise<Achievement> {
    const response = await this.request<Achievement>(`achievements/${id}`);
    return response.data;
  }

  // Settings
  async getSiteSettings(): Promise<SiteSettings> {
    const response = await this.request<SiteSettings>('settings');
    return response.data;
  }

  // Stats
  async getStats(): Promise<Stats> {
    const response = await this.request<Stats>('stats.php');
    return response.data;
  }

  async getCountdownSettings(): Promise<CountdownSettings> {
    const response = await this.request<CountdownSettings>('countdown.php');
    return response.data;
  }

  async updateCountdownSettings(settings: Partial<CountdownSettings>): Promise<CountdownSettings> {
    const response = await this.request<CountdownSettings>('countdown.php', {
      method: 'PUT',
      body: JSON.stringify(settings)
    });
    return response.data;
  }
}

export const apiService = new ApiService();
export default apiService;
