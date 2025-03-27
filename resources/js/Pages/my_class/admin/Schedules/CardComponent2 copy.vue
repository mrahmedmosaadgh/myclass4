<script setup>
defineProps({
  title: {
    type: String,
    default: "Card Title",
  },
  content: {
    type: String,
    default: "This is a sample card description.",
  },
  image: {
    type: String,
    default: "https://via.placeholder.com/300",
  },
  buttonText: {
    type: String,
    default: "Learn More",
  },
  buttonColor: {
    type: String,
    default: "primary", // primary, secondary, tertiary, neutral
  },
});
</script>

<template>
  <div class="Button_Button__u2RFO  bg-white rounded-xl      p-2 transition-all duration-300 hover:shadow-2xl">
    <!-- Card Image -->
    <!-- <img :src="image" alt="Card Image" class="w-full h-40 object-cover rounded-lg" /> -->

    <!-- Card Content -->
    <div class="mt-4">
      <h3 class="text-lg font-semibold">{{ title }}</h3>
      <p class="mt-2 text-gray-600">{{ content }}</p>
    </div>

    <!-- Neumorphic Button -->
    <button
      class="Button_Button__u2RFO mt-4 w-full"
      :data-color="buttonColor"
    >
      {{ buttonText }}
    </button>
  </div>
</template>

<style scoped>
 .Button_Button__u2RFO {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
    flex: 1;
    border-radius: var(--radius-md);
    padding: 12px;
    background: #f4f4f4;
    box-shadow: inset 1px 1px 1px 0 rgb(255,255,255),
        inset -1px -1px 1px 0 rgba(0,0,0,.15),
        .444584px .444584px .628737px -1px rgba(0,0,0,.26),
        1.21072px 1.21072px 1.71222px -1.5px rgba(0,0,0,.247),
        2.6583px 2.6583px 3.75941px -2.25px rgba(0,0,0,.23),
        5.90083px 5.90083px 8.34503px -3px rgba(0,0,0,.192),
        10px 10px 21.2132px -3.75px rgba(0,0,0,.056),
        -.5px -.5px 0 0 rgb(0 0 0/5%);
    transition: box-shadow .3s ease;
    cursor: pointer;
    user-select: none;
}

.Button_Button__u2RFO[data-block] {
    width: 100%;
}

.Button_Button__u2RFO[data-disabled] {
    cursor: not-allowed;
}

.Button_Button__u2RFO:active,
.Button_Button__u2RFO[data-selected] {
    box-shadow: inset .5px .5px 1px #fff,
        inset -.5px -.5px 1px #00000026,
        .222px .222px .314px -.5px #0003,
        .605px .605px .856px -1px #0000002e,
        1.329px 1.329px 1.88px -1.5px #00000040,
        2.95px 2.95px 4.172px -2px #0000001a,
        2.5px 2.5px 3px -2.5px #00000026,
        -.5px -.5px 0 0 rgb(0 0 0/10%);
}

.Button_Button__u2RFO[data-color=primary] {
    color: #fff;
    background: var(--primary);
    box-shadow: inset 1px 1px 1px #ffffffd4,
        inset -1px -1px 1px #0000003b,
        .444584px .444584px .628737px -1px #00000042,
        1.21072px 1.21072px 1.71222px -1.5px #0000003f,
        2.6583px 2.6583px 3.75941px -2.25px #0000003b,
        5.90083px 5.90083px 8.34503px -3px #00000031,
        10px 10px 21.2132px -3.75px #0000003b,
        -.5px -.5px #952b0087;
}

.Button_Button__u2RFO[data-color=primary]:active,
.Button_Button__u2RFO[data-color=primary][data-selected] {
    box-shadow: inset .5px .5px 1px #fff,
        inset -.5px -.5px 1px #0000005b,
        .222px .222px .314px -1px #0003,
        .605px .605px .856px -1px #0000002e,
        1.329px 1.329px 1.88px -1.5px #00000040,
        2.95px 2.95px 4.172px -2px #0000001a,
        2.5px 2.5px 3px -2.5px #00000026,
        -.5px -.5px #00000022;
}

 


/* Additional styles for other button variants (secondary, tertiary, neutral) remain unchanged */

.Footer_Footer__ElFDz {
    position: fixed;
    bottom: 0;
    right: 0;
    left: 0;
    z-index: 20;
    border-top: 1px solid transparent;
    background: #ececec70;
    backdrop-filter: blur(2rem);
    -webkit-backdrop-filter: blur(2rem);
    font-size: 1.125rem;
    transition: border .2s ease;
}

[data-scrollable=true] .Footer_Footer__ElFDz {
    border-top: 1px solid #fff;
}

@keyframes Footer_wave__VZ9GE {
    0%, to {
        opacity: 0;
    }
    50% {
        opacity: 1;
    }
}

.Footer_animate-wave__7QS_7 {
    animation: Footer_wave__VZ9GE 1.2s cubic-bezier(.37,0,.63,1) infinite;
    opacity: 1;
}

.Switcher_Track__RYRxz {
    position: relative;
    width: 42px;
    height: 25px;
    border-radius: 100px;
    box-shadow: inset 0 1px 2px #00000026, 0 1px 0 0 #ffffff40;
    background: rgba(0,0,0,.08);
    cursor: pointer;
    transition: background-color .25s ease;
    user-select: none;
}

.Switcher_Track__RYRxz[data-state=checked] {
    background: var(--primary);
}

.Switcher_Thumb__Au9Bn {
    display: block;
    width: 21px;
    height: 21px;
    transform: translateX(2px);
    border-radius: 100%;
    transition: transform .25s ease;
    will-change: transform;
    background: #f4f4f4;
    box-shadow: inset 1px 1px 1px #fff,
        inset -1px -1px 0 #0000001a,
        .444584px .444584px .628737px -.75px #00000042,
        1.21072px 1.21072px 1.71222px -1.5px #0000003f,
        2.6583px 2.6583px 3.75941px -2.25px #0000003b,
        5.90083px 5.90083px 8.34503px -3px #00000031,
        10px 10px 21.2132px -3.75px #0000000e;
}

.Switcher_Track__RYRxz[data-state=checked] .Switcher_Thumb__Au9Bn {
    transform: translateX(19px);
}

.DevMode_Container__y38as {
    overflow: hidden;
    box-shadow: var(--shadow-textarea);
    border-radius: var(--radius-md);
}

 </style>
