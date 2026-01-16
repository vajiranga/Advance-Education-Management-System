<template>
  <q-page :class="$q.dark.isActive ? 'q-pa-md bg-dark-page' : 'q-pa-md bg-grey-1'">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold" :class="$q.dark.isActive ? 'text-deep-purple-2' : 'text-deep-purple'">Messages & Communications</div>
    </div>

    <div class="row q-col-gutter-md">
       <!-- Contact List -->
       <div class="col-12 col-md-4">
          <q-card class="full-height no-shadow border-light" :class="$q.dark.isActive ? 'bg-dark border-dark' : 'bg-white'">
             <q-card-section>
                <q-input outlined dense v-model="search" placeholder="Search teachers..." class="q-mb-md" :dark="$q.dark.isActive" :bg-color="$q.dark.isActive ? 'grey-9' : ''">
                   <template v-slot:prepend><q-icon name="search" /></template>
                </q-input>
                
                <q-list separator :dark="$q.dark.isActive">
                   <q-item 
                      clickable 
                      v-ripple 
                      v-for="contact in contacts" 
                      :key="contact.id" 
                      @click="activeContact = contact" 
                      :active="activeContact?.id === contact.id" 
                      :active-class="$q.dark.isActive ? 'bg-deep-purple-9 text-deep-purple-2' : 'bg-deep-purple-1 text-deep-purple'"
                      class="rounded-borders transition-generic"
                   >
                      <q-item-section avatar>
                         <q-avatar size="42px">
                            <img :src="contact.avatar">
                         </q-avatar>
                      </q-item-section>
                      <q-item-section>
                         <q-item-label class="text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ contact.name }}</q-item-label>
                         <q-item-label caption :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey-7'">{{ contact.role }}</q-item-label>
                      </q-item-section>
                      <q-item-section side>
                        <q-badge rounded color="green" v-if="contact.online" />
                      </q-item-section>
                   </q-item>
                </q-list>
             </q-card-section>
          </q-card>
       </div>

       <!-- Chat Area -->
       <div class="col-12 col-md-8">
          <q-card class="full-height column no-shadow border-light" style="min-height: 500px" :class="$q.dark.isActive ? 'bg-dark border-dark' : ''">
             <!-- Chat Header -->
             <q-card-section class="row items-center" :class="$q.dark.isActive ? 'bg-dark-page' : 'bg-grey-2'">
                <div v-if="activeContact" class="row items-center">
                   <q-avatar size="36px" class="q-mr-sm">
                      <img :src="activeContact.avatar">
                   </q-avatar>
                   <div>
                       <div class="text-subtitle1 text-weight-bold" :class="$q.dark.isActive ? 'text-white' : ''">{{ activeContact.name }}</div>
                       <div class="text-caption" :class="$q.dark.isActive ? 'text-grey-4' : 'text-grey'">{{ activeContact.role }}</div>
                   </div>
                </div>
                <div v-else class="text-grey">Select a contact to start chatting</div>
             </q-card-section>

             <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

             <!-- Messages -->
             <q-card-section class="col scroll q-pa-md" :class="$q.dark.isActive ? 'bg-dark' : 'bg-white'">
                <div v-if="activeContact">
                   <q-chat-message
                     v-for="msg in messages"
                     :key="msg.id"
                     :name="msg.sent ? 'Me' : activeContact.name"
                     :text="[msg.text]"
                     :sent="msg.sent"
                     :bg-color="msg.sent ? ($q.dark.isActive ? 'deep-purple-9' : 'deep-purple-1') : ($q.dark.isActive ? 'grey-9' : 'grey-2')"
                     :text-color="msg.sent ? ($q.dark.isActive ? 'white' : 'black') : ($q.dark.isActive ? 'white' : 'black')"
                   />
                </div>
                <div v-else class="flex flex-center full-height">
                   <div class="text-center text-grey">
                      <q-icon name="chat_bubble_outline" size="64px" class="q-mb-md" />
                      <div class="text-h6">Start a conversation</div>
                   </div>
                </div>
             </q-card-section>

             <q-separator :class="$q.dark.isActive ? 'bg-grey-8' : ''" />

             <!-- Input Area -->
             <q-card-section :class="$q.dark.isActive ? 'bg-dark-page' : 'bg-grey-1'" v-if="activeContact">
                <q-input 
                    outlined 
                    dense 
                    v-model="newMessage" 
                    placeholder="Type a message..." 
                    @keyup.enter="sendMessage" 
                    :bg-color="$q.dark.isActive ? 'grey-9' : 'white'" 
                    :dark="$q.dark.isActive"
                >
                   <template v-slot:after>
                      <q-btn round flat icon="send" :color="$q.dark.isActive ? 'deep-purple-2' : 'deep-purple'" @click="sendMessage" />
                   </template>
                </q-input>
             </q-card-section>
          </q-card>
       </div>
    </div>
  </q-page>
</template>

<script setup>
import { ref } from 'vue'

const search = ref('')
const newMessage = ref('')

const contacts = ref([
 { id: 1, name: 'Mr. Bandara', role: 'Class Teacher - Grade 10', avatar: 'https://cdn.quasar.dev/img/avatar1.jpg', online: true },
 { id: 2, name: 'Mrs. Silva', role: 'Science Teacher', avatar: 'https://cdn.quasar.dev/img/avatar3.jpg', online: false },
 { id: 3, name: 'Admin Office', role: 'Support', avatar: 'https://cdn.quasar.dev/img/boy-avatar.png', online: true }
])

const activeContact = ref(contacts.value[0])

const messages = ref([
  { id: 1, text: 'Good morning Mr. Bandara, I wanted to ask about Kasun\'s attendance.', sent: true },
  { id: 2, text: 'Good morning Mr. Gunawardena. Kasun has been attending classes regularly.', sent: false }
])

const sendMessage = () => {
   if (!newMessage.value) return;
   messages.value.push({
      id: Date.now(),
      text: newMessage.value,
      sent: true
   })
   newMessage.value = ''
}
</script>

<style scoped>
.border-light {
   border: 1px solid #ddd;
}
.transition-generic {
   transition: all 0.3s ease;
}
</style>
