export default defineAppConfig({
  ui: {
    colors: {
      primary: 'green',
      neutral: 'slate'
    },
    input: {
      defaultVariants: {
        size: 'lg'
      },
      slots: {
        root: 'relative inline-flex w-full items-center'
      }
    },
    formField: {
      defaultVariants: {
        size: 'lg'
      },
      slots: {
        root: 'w-full',
        wrapper: 'w-full',
        container: 'relative w-full'
      }
    }
  }
})
