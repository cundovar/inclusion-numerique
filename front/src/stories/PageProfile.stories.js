import Profile from '../views/Profile.vue';


export default {
  title: 'PAGE/PageProfile',
  component: Profile,
  tags: ['autodocs'],
  parameters: {
    layout: 'fullscreen',
  },


};

export const Default = {
  args: {
    user: {
      id: 1,
      username: 'JohnDoe',
      email: 'john@example.com',
    },
  },
};
