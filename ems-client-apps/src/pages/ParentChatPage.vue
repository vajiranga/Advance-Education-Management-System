<template>
  <q-page class="q-pa-md bg-grey-1">
    <div class="row items-center justify-between q-mb-lg">
      <div class="text-h5 text-weight-bold text-deep-purple">Messages & Communications</div>
    </div>

    <div class="row q-col-gutter-md">
       <!-- Contact List -->
       <div class="col-12 col-md-4">
          <q-card class="full-height no-shadow border-light bg-white">
             <q-card-section>
                <q-input outlined dense v-model="search" placeholder="Search teachers..." class="q-mb-md">
                   <template v-slot:prepend><q-icon name="search" /></template>
                </q-input>
                
                <q-list separator>
                   <q-item 
                      clickable 
                      v-ripple 
                      v-for="contact in contacts" 
                      :key="contact.id" 
                      @click="activeContact = contact" 
                      :active="activeContact?.id === contact.id" 
                      active-class="bg-deep-purple-1 text-deep-purple"
                      class="rounded-borders transition-generic"
                   >
                      <q-item-section avatar>
                         <q-avatar size="42px">
                            <img :src="contact.avatar">
                         </q-avatar>
                      </q-item-section>
                      <q-item-section>
                         <q-item-label class="text-weight-bold">{{ contact.name }}</q-item-label>
                         <q-item-label caption class="text-grey-7">{{ contact.role }}</q-item-label>
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
          <q-card class="full-height column no-shadow border-light" style="min-height: 500px">
             <!-- Chat Header -->
             <q-card-section class="bg-grey-2 row items-center">
                <div v-if="activeContact" class="row items-center">
                   <q-avatar size="36px" class="q-mr-sm">
                      <img :src="activeContact.avatar">
                   </q-avatar>
                   <div>
                       <div class="text-subtitle1 text-weight-bold">{{ activeContact.name }}</div>
                       <div class="text-caption text-grey">{{ activeContact.role }}</div>
                   </div>
                </div>
                <div v-else class="text-grey">Select a contact to start chatting</div>
             </q-card-section>

             <q-separator />

             <!-- Messages -->
             <q-card-section class="col scroll q-pa-md bg-white">
                <div v-if="activeContact">
                   <q-chat-message
                     v-for="msg in messages"
                     :key="msg.id"
                     :name="msg.sent ? 'Me' : activeContact.name"
                     :text="[msg.text]"
                     :sent="msg.sent"
                     :bg-color="msg.sent ? 'deep-purple-1' : 'grey-2'"
                     :text-color="msg.sent ? 'black' : 'black'"
                   />
                </div>
                <div v-else class="flex flex-center full-height">
                   <div class="text-center text-grey">
                      <q-icon name="chat_bubble_outline" size="64px" class="q-mb-md" />
                      <div class="text-h6">Start a conversation</div>
                   </div>
                </div>
             </q-card-section>

             <q-separator />

             <!-- Input Area -->
             <q-card-section class="bg-grey-1" v-if="activeContact">
                <q-input outlined dense v-model="newMessage" placeholder="Type a message..." @keyup.enter="sendMessage" bg-color="white">
                   <template v-slot:after>
                      <q-btn round flat icon="send" color="deep-purple" @click="sendMessage" />
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
