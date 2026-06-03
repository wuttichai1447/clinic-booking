export interface Clinic {
  id: string
  name: string
  address: string
  phone: string
  image?: string
}

export interface Service {
  id: string
  name: string
  duration: number
  price: number
  clinicId?: string | null
  image?: string
}

export interface Therapist {
  id: string
  name: string
  specialty?: string
  clinicId?: string | null
  image?: string
}

export interface TimeSlot {
  id: string
  time: string
  available: boolean
  therapistId?: string
}

export type PaymentMethod = 'credit_card' | 'transfer' | 'promptpay'

export interface Appointment {
  id?: string
  clinicId: string
  serviceId: string
  therapistId: string
  date: string
  timeSlotId: string
  customerName: string
  customerPhone: string
  customerEmail?: string
  notes?: string
  status?: 'pending' | 'awaiting_payment' | 'awaiting_verification' | 'confirmed' | 'cancelled' | 'completed'
  subtotal?: number
  discountAmount?: number
  amount?: number
  promotionId?: number | null
  promotionCode?: string | null
  createdAt?: string
  paymentMethod?: PaymentMethod
  paidAt?: string
  clinicName?: string
  serviceName?: string
  therapistName?: string
}

export interface PricingPreview {
  promotionId: number
  promotionCode: string
  promotionTitle: string
  subtotal: number
  discountAmount: number
  amount: number
}

export interface BookingInvoice {
  invoiceNumber: string
  issuedAt: string
  customer: { name: string; phone: string; email?: string }
  booking: Appointment
  lineItems: { description: string; amount: number }[]
  subtotal: number
  discountAmount: number
  total: number
  currency: string
  paymentNote: string
  stripeReady: boolean
}

export interface BookingState {
  currentStep: number
  clinic: Clinic | null
  service: Service | null
  therapist: Therapist | null
  selectedDate: string | null
  timeSlot: TimeSlot | null
  customerName: string
  customerPhone: string
  customerEmail: string
  notes: string
}
