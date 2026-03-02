import { ref, onMounted, onUnmounted, Ref } from 'vue'

export function useAnimateOnScroll<T extends HTMLElement>() {
  const elementRef = ref<T | null>(null)
  const isVisible = ref(false)

  let observer: IntersectionObserver

  onMounted(() => {
    if (!elementRef.value) return

    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            isVisible.value = true
            observer.unobserve(entry.target) // animate once
          }
        })
      },
      { threshold: 0.1 }
    )

    observer.observe(elementRef.value)
  })

  onUnmounted(() => {
    if (observer && elementRef.value) observer.unobserve(elementRef.value)
  })

  return [elementRef, isVisible] as const
}
